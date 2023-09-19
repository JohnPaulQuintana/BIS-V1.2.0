<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class NlpController extends Controller
{
    public function process(Request $request)
    {
        // $url = env('APP_URL').'/nlp';
        $client = new Client();
        $response = $client->post('http://localhost:8000/nlp', [
            'json' => ['text' => $request->input('text')]
        ]);

        $result = json_decode($response->getBody(), true);

        // Handle the NLP response
        $modelClass = 'App\\Models\\' .$result['answer']['model'];

        // for all employee
        if(isset($result['answer']['exclude'])){
           $data = $modelClass::where('username', '!=', $result['answer']['exclude'])->get();
        }

        // for all present
        if(isset($result['answer']['include-employee'])){
            $data = $modelClass::join('users', 'users.id', '=', $result['answer']['table'] . '.' . $result['answer']['include-employee'])
            ->where('users.id', '=', $result['answer']['include-employee'])
            ->get();

            $data = \App\Models\User::whereHas($result['answer']['table'], function ($query) {
                $query->whereNotNull('employee_id')
                        ->whereDate('time_in', Carbon::now()->toDate()) // Filter by today's date
                      ->latest('time_in'); // Order by the time_in column in descending order
            })->with([$result['answer']['table'] => function ($query) {
                $query->whereNotNull('employee_id')
                      ->latest('time_in')
                      ->first(); // Get the latest employee record
            }])->get();

             // Loop through the data and extract desired values
             $formattedResult = '';
             $count = count($data);
             $total = "We have a total of {$count} Present Employee.  im going to navigate you to a Employee Table. ";
             $formattedResult = [
                // 'answer' =>$total. implode(', ', $formattedResponse),
                'answer' => $total,
                'init' => 'Initializing Employee Tables...'.$total,
                'action' => 'employee.present'
            ];
            return response()->json($formattedResult);
        }

        // for all product stocks not 0
        if(isset($result['answer']['include-stock'])){
            $data = $modelClass::where($result['answer']['include-stock'], '!=', 0)->get();
             // Loop through the data and extract desired values
             $formattedResult = '';
             $count = count($data);
             $total = "We have a product total of {$count}.  Available products displayed on the table below. ";

            //  foreach ($data as $item) {
            //      // Adjust field names based on your data structure
            //      $formattedResult .= " {$item->product_type} type. </br> with the name {$item->product_name}. </br> and available stocks {$item->stocks}.\n";
            //  }
 
             // Format the result as a single text string
            //  $formattedResponse = [
            //      'answer' => $formattedResult
            //  ];

            // dd($formattedResponse);
                // ...
                // Remove the trailing comma and whitespace
                $formattedResult = [
                    // 'answer' =>$total. implode(', ', $formattedResponse),
                    'answer' => $total,
                    'init' => 'Accesing your dashboard... Initializing Tables...'.$total,
                    'action' => 'available'
                ];

                return response()->json($formattedResult);
        }

        // for printing available products
        if(isset($result['answer']['include-print'])){
            $data = $modelClass::where($result['answer']['include-print'], '!=', 0)->get();
             // Loop through the data and extract desired values
             $formattedResult = '';
             $count = count($data);
             $total = "We have a product total of {$count}.  Available products displayed on the table below. initializing print process.";

            //  foreach ($data as $item) {
            //      // Adjust field names based on your data structure
            //      $formattedResult .= " {$item->product_type} type. </br> with the name {$item->product_name}. </br> and available stocks {$item->stocks}.\n";
            //  }
 
             // Format the result as a single text string
            //  $formattedResponse = [
            //      'answer' => $formattedResult
            //  ];

            // dd($formattedResponse);
                // ...
                // Remove the trailing comma and whitespace
                $formattedResult = [
                    // 'answer' =>$total. implode(', ', $formattedResponse),
                    'answer' => $total,
                    'init' => 'Accesing your dashboard... Initializing Tables...'.$total,
                    'action' => 'print.products'
                ];

                return response()->json($formattedResult);
        }

        // for attendance
        if(isset($result['answer']['include-attendance'])){
            $formattedResult = [
                'answer' =>$result['answer']['include-attendance'],
                'init'=>'Accesing your dashboard... Initializing camera... Activation Completed',
                'action' =>'attendance'
            ];
            return response()->json($formattedResult);
        }
        // dd($data);

       

        // return response()->json($formattedResult);
    }
}
