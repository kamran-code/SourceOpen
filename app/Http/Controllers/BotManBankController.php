<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Support\Facades\Http;

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
}
