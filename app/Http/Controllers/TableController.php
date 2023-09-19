<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Rejected;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function availableStocks(){
        $stocks = Inventory::where('stocks', '!=', 0)->get();
        $out = Inventory::where('stocks', '=', 0)
            ->orderBy('created_at','desc')
            ->get();
        return view('admin.inventory.inventory', ['stocks'=>$stocks, 'notifs'=>$out]);
    }

    public function purchasedProduct(){
        $invoices = Invoice::with('inventory') // Load the related products
        ->orderBy('date', 'desc')
        ->get();

        $out = Inventory::where('stocks', '=', 0)
            ->orderBy('created_at','desc')
            ->get();

        // Calculate the date one day ago
        $oneDayAgo = Carbon::now()->subDay();
        // Create a Carbon instance for the current date and time
        $currentDateTime = Carbon::now();

        // Get only the date portion in "Y-m-d" format
        $dateOnly = $currentDateTime->format('Y-m-d');

        $newest = Invoice::where('date', $dateOnly)
        ->orderBy('date', 'asc')
        ->get();
        // Retrieve the oldest invoice from one day ago
        $oldest = Invoice::where('date', '<', $oneDayAgo)
            ->orderBy('date', 'asc')
            ->get();
        return view('admin.inventory.purchased', ['invoices'=>$invoices, 'newest'=>$newest, 'oldest'=>$oldest, 'today'=>$dateOnly, 'notifs'=>$out]);
    }

    public function rejectedProduct(){
        $rejected = Rejected::orderBy('created_at','desc')->get();
        $out = Inventory::where('stocks', '=', 0)
            ->orderBy('created_at','desc')
            ->get();
        return view('admin.inventory.rejected', ['rejected'=>$rejected, 'notifs'=>$out]);
    }

    public function outofstocksProduct(){
        $out = Inventory::where('stocks', '=', 0)
            ->orderBy('created_at','desc')
            ->get();
        return view('admin.inventory.out-of-stocks', ['outofstocks'=>$out, 'notifs'=>$out]);
    }

    public function processProduct(Request $request){
        // Use the $request object to access the 'ids' parameter
        $ids = $request->route('id');
        // Convert the comma-separated string of IDs to an array
        $idArray = explode(',', $ids);

        // Use the array of IDs to retrieve data from the database
        $inventory = Inventory::whereIn('id', $idArray)
            ->where('stocks', '!=', 0)
            ->get(); // Retrieve all matching records
        // dd($inventory);
        return view('admin.inventory.sold', ['inventories'=>$inventory]);
    }
}
