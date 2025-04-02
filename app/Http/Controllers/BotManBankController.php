<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Support\Facades\Http;
use BotMan\BotMan\Interfaces\Middleware\Received;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingNotification;
use BotMan\BotMan\Middleware\Dialogflow;


class MessageHistoryMiddleware implements Received
{
    public function received(\BotMan\BotMan\Messages\Incoming\IncomingMessage $message, $next, BotMan $bot)
    {
        $userId = $message->getSender();
        $userMessage = $message->getText();
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        $history = Cache::get('user_messages_' . $userId, '');
        $newEntry = "User ({$timestamp}): {$userMessage}\n";

        $updatedHistory = $newEntry . $history;

        // Truncate history if too long
        if (strlen($updatedHistory) > 2000) {
            $updatedHistory = substr($updatedHistory, 0, 2000);
        }

        Cache::put('user_messages_' . $userId, $updatedHistory, now()->addDays(7));
        return $next($message);
    }
}


class BotManBankController extends Controller
{
    //
    public function handle(Request $request)
    {
        // Load Web Driver
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        // Load BotMan configuration
        $config = config('botman');

        // Create BotMan instance with cache
        $botman = BotManFactory::create($config, new LaravelCache());



        // Basic greetings
        $botman->hears('hi|hello|hey', function (BotMan $bot) {
            $responses = [
                "Hello! Welcome to CBmgarh. How can I assist you today?",
                "Hi there! How can I help with your CBmgarh-related query?",
                "Hey! I'm here to provide information about CBmgarh. What would you like to know?"
            ];
            $bot->reply($responses[array_rand($responses)]);
        });

        // Capture user's name
        $botman->hears('my name is {name}', function (BotMan $bot, $name) {
            $bot->reply('Nice to meet you, ' . $name . '!');
        });

        // Handle "ask my name" request without calling a non-existent method
        $botman->hears('ask my name', function (BotMan $bot) {
            $bot->ask('Hello! What is your name?', function (Answer $answer, $conversation) {
                $name = $answer->getText();
                $conversation->say('Nice to meet you, ' . $name . '!');
            });
        });

        $botman->fallback(function (BotMan $bot) use ($request) {
            $userMessage = $request->input('message');


            $bot->typesAndWaits(2);
            // Call OpenAI API
            $openAIResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('TOGETHER_API_KEY'),
            ])->withOptions(["verify" => false])
                ->post('https://api.together.xyz/v1/chat/completions', [
                    'model' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => "You are CBMgarh's official chatbot. You are a friendly and helpful chatbot for The Mahendragarh Central Co-operative Bank Ltd.
                         Always respond in a conversational and human-like manner. 
                         Keep responses **short and to the point** while still being helpful.
                          You can greet users, answer questions about CBMgarh, and provide details like services, branch locations, IFSC codes, contact details, and business hours. 
                          If a user asks about something unrelated to CBMgarh, politely say: 'I'm here to help with CBMgarh-related questions.
                           Let me know how I can assist you!'\n\n##
                            **Bank Overview**\nCBMgarh is a district-level cooperative bank serving the Mahendragarh region in Haryana, India.
                             We offer a range of banking services to meet the financial needs of our community.
                             \n\n## **Services Offered**\n- **Deposit Accounts**: Savings Accounts, Current Accounts, Fixed Deposits\n-
                              **Loan Products**: Aatmanirbhar Loan, CC Traders Loan, Education Loan, Home Loan, Personal Loan, SHG & JLGs Loans\n- 
                              **Banking Services**: Core Banking Solutions (CBS), Cheque Transaction System (CTS), NEFT/RTGS, SMS Alerts, Locker Facility\n- 
                              **Debit Cards**: RuPay Debit Card, RuPay Kisan Card, Green PIN Service, E-Commerce, Card Control, RuPay Card Offers\n- 
                              **Government Schemes**: PMSBY, PMJJBY, PMJDY, APY\n\n## **Branch Network**\nCBMgarh operates multiple branches across the Mahendragarh district, including:\n- 
                              **Head Office**: Near Anaj Mandi, Railway Road, Mahendragarh, Pin 123029\n- **Other Branches**: Narnaul, Nizampur, Nangal Choudhary, Balaha Kalan, City Mahendergarh,
                               Dongra Ahir, Bhojawas (Nangal Choudhary), Akoda, Kanina, Bachhod, Bhojawas (Kanina), Gudha, Khatodra, Nasibpur, Nangal Dargu, Shima\n\n## 
                               **IFSC Code**\nThe bank's IFSC code is **UTIB0SMCCB1**.\n\n## **Contact Information**\n- 
                               **Head Office**: Near Anaj Mandi, Railway Road, Mahendragarh, Haryana, India, Pincode: 123029\n- **Phone**: 01285-220101\n- **Email**: itcell@cbmgarh.com\n- **Website**: [cbmgarh.com](https://cbmgarh.com)\n\n##
                                **Business Hours**\n- **Monday - Saturday**: 10:00 AM - 5:00 PM\n- **2nd and 4th Saturdays**: Closed\n- **Sunday**: Closed\n\n## 
                                **For unrelated questions, politely respond:**\n'Sorry, I can only assist with questions about CBMgarh. Let me know how I can help!'"],

                        ['role' => 'user', 'content' => $userMessage]
                    ],

                    'max_tokens' => 250,
                    'temperature' => 0.1,
                ]);

            $responseData = $openAIResponse->json();

            if (isset($responseData['choices'][0]['message']['content'])) {
                $bot->reply($responseData['choices'][0]['message']['content']);
            } else {
                // Handle error response
                $errorMsg = $responseData['error']['message'] ?? 'Something went wrong with the AI API.';
                $bot->reply("Error: " . $errorMsg);
            }
        });

        // Listen for input
        $botman->listen();
    }

    public function handle_triptoll(Request $request)
    {
        // Load Web Driver
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        // BotMan Configuration
        $config = config('botman');

        // Create BotMan instance
        $botman = BotManFactory::create($config, new LaravelCache());

        // Start Chatbot with Buttons
        $botman->hears('hi|Hello|start', function (BotMan $bot) {
            $question = Question::create("👋 Welcome to Triptoll Packers and Movers! How can I assist you today?")
                ->addButtons([
                    Button::create('📦 Book a Move')->value('book_move'),
                    Button::create('ℹ️ Get Pricing Info')->value('get_pricing'),
                    Button::create('📞 Contact Support')->value('contact_support'),
                ]);

            $bot->reply($question);
        });

        $botman->hears('get_pricing', function (BotMan $bot) {
            $bot->reply("💰 Our pricing depends on distance, items, and additional services. Please visit our website or call us at +91 9876543210 for a free quote.");
        });
        
       
        $botman->hears('contact_support', function (BotMan $bot) {
            $bot->reply("📞 You can reach our support team at +91 9876543210 or email us at support@triptoll.com. We're here to help!");
        });

        // Handle "Book a Move" Click
        $botman->hears('book_move', function (BotMan $bot) {
            $question = Question::create("📅 Select your move date:")
                ->addButtons([
                    Button::create('Tomorrow')->value('move_tomorrow'),
                    Button::create('Next Week')->value('move_next_week'),
                    Button::create('Next Month')->value('move_next_month'),
                ]);

            $bot->reply($question);
        });

        // Explicitly handle each move date selection
        $botman->hears('move_tomorrow', function (BotMan $bot) {
            Cache::put('move_date', 'Tomorrow', now()->addMinutes(30));

            $question = Question::create("📍 Where is your pickup location?")
                ->addButtons([
                    Button::create('📍 Delhi NCR')->value('location_delhi'),
                    Button::create('📍 Mumbai')->value('location_mumbai'),
                    Button::create('📍 Bangalore')->value('location_bangalore'),
                ]);

            $bot->reply($question);
        });

        $botman->hears('move_next_week', function (BotMan $bot) {
            Cache::put('move_date', 'Next Week', now()->addMinutes(30));

            $question = Question::create("📍 Where is your pickup location?")
                ->addButtons([
                    Button::create('📍 Delhi NCR')->value('location_delhi'),
                    Button::create('📍 Mumbai')->value('location_mumbai'),
                    Button::create('📍 Bangalore')->value('location_bangalore'),
                ]);

            $bot->reply($question);
        });

        $botman->hears('move_next_month', function (BotMan $bot) {
            Cache::put('move_date', 'Next Month', now()->addMinutes(30));

            $question = Question::create("📍 Where is your pickup location?")
                ->addButtons([
                    Button::create('📍 Delhi NCR')->value('location_delhi'),
                    Button::create('📍 Mumbai')->value('location_mumbai'),
                    Button::create('📍 Bangalore')->value('location_bangalore'),
                ]);

            $bot->reply($question);
        });

        // Handle Location Selection
        $botman->hears('location_delhi|location_mumbai|location_bangalore', function (BotMan $bot) {
            $location = $bot->getMessage()->getText(); // Get the clicked button value
        
            $locationMap = [
                'location_delhi' => 'Delhi NCR',
                'location_mumbai' => 'Mumbai',
                'location_bangalore' => 'Bangalore'
            ];
        
            if (!isset($locationMap[$location])) {
                $bot->reply("❌ Invalid location. Please select a valid location.");
                return;
            }
        
            $selectedLocation = $locationMap[$location];
        
            Cache::put('pickup_location', $selectedLocation, now()->addMinutes(30));
        
            $bot->reply("✅ Move booked! 🎉 We will pick up your items from **{$selectedLocation}**. You will receive an email confirmation shortly.");
        });
        

        // Listen for input
        $botman->listen();
    }

    // **Helper function for Move Date question**
    private function moveDateQuestion()
    {
        return Question::create("📅 Select your move date:")
            ->addButtons([
                Button::create('Tomorrow')->value('Tomorrow'),
                Button::create('Next Week')->value('Next Week'),
                Button::create('Next Month')->value('Next Month'),
            ]);
    }

    // **Helper function for Pickup Location question**
    private function pickupLocationQuestion()
    {
        return Question::create("📍 Where is your pickup location?")
            ->addButtons([
                Button::create('Mumbai')->value('Mumbai'),
                Button::create('Delhi')->value('Delhi'),
                Button::create('Bangalore')->value('Bangalore'),
            ]);
    }

    // **Helper function for Destination question**
    private function destinationQuestion()
    {
        return Question::create("➡️ Where do you want to move?")
            ->addButtons([
                Button::create('Pune')->value('Pune'),
                Button::create('Chennai')->value('Chennai'),
                Button::create('Hyderabad')->value('Hyderabad'),
            ]);
    }

    // **Helper function for Confirm Booking question**
    private function confirmBookingQuestion($moveDate, $pickupLocation, $destination)
    {
        return Question::create("✅ Confirm your booking:\n📅 Date: $moveDate\n📍 From: $pickupLocation\n➡️ To: $destination")
            ->addButtons([
                Button::create('✅ Confirm Booking')->value('confirm'),
                Button::create('❌ Cancel')->value('cancel'),
            ]);
    }
}
