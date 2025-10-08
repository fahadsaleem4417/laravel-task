<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StockPricesImport;

class StockPriceController extends Controller
{
    public function showForm()
    {
        return view('stock_price_import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::queueImport(new StockPricesImport, $request->file('file'));

            return back()->with('success', 'Stock data import has been queued and is processing in the background.');

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }


}
