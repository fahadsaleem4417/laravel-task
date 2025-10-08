<?php

namespace App\Http\Controllers;

use App\Models\StockPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StockPriceApiController extends Controller
{
    public function getPerformance()
    {
        $latestData = StockPrice::orderBy('date', 'desc')->first();

        if (!$latestData) {
            return response()->json(['error' => 'No stock data found in the database.'], 404);
        }

        $latestPrice = (float)$latestData->stock_price;
        $latestDate = Carbon::parse($latestData->date);

        $periods = ['1D', '1M', '3M', '6M', 'YTD', '1Y', '3Y', '5Y', '10Y', 'MAX'];
        $results = [];

        foreach ($periods as $period) {
            $startDate = $this->calculateStartDate($latestDate, $period);

            if ($period === 'MAX') {
                $startPriceData = StockPrice::orderBy('date', 'asc')->first();
            } else {

                $startPriceData = StockPrice::whereDate('date', '<=', $startDate->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->first();
            }

            if ($startPriceData) {
                $startPrice = (float)$startPriceData->stock_price;


                $valueChange = $latestPrice - $startPrice;
                $percentageChange = ($startPrice != 0) ? ($valueChange / $startPrice) * 100 : 0;


                $results[$period] = [
                    'start_date' => $startPriceData->date,
                    'start_price' => round($startPrice, 4),
                    'latest_price' => round($latestPrice, 4),
                    'value_change' => round($valueChange, 4),
                    'percentage_change' => round($percentageChange, 2) . '%',
                ];
            } else {
                 $results[$period] = [
                    'error' => 'Historical data not available for this period.',
                ];
            }
        }

        return response()->json([
            'reference_date' => $latestData->date,
            'performance' => $results
        ]);
    }

    private function calculateStartDate(Carbon $latestDate, string $period): Carbon
    {
        $date = $latestDate->copy();

        switch ($period) {
            case '1D': return $date->subDay();
            case '1M': return $date->subMonthNoOverflow();
            case '3M': return $date->subMonths(3);
            case '6M': return $date->subMonths(6);
            case '1Y': return $date->subYearNoOverflow();
            case '3Y': return $date->subYears(3);
            case '5Y': return $date->subYears(5);
            case '10Y': return $date->subYears(10);
            case 'YTD': return $date->startOfYear();
            default:
                return $date;
        }
    }

    public function getChangeBetweenDates(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
        ]);

        $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d');
        $endDate = Carbon::parse($request->input('end_date'))->format('Y-m-d');

        if ($startDate >= $endDate) {
            return response()->json([
                'error' => 'The start_date must be before the end_date.'
            ], 422);
        }


        $startPriceData = StockPrice::whereDate('date', '<=', $startDate)
            ->orderBy('date', 'desc')
            ->first();


        $endPriceData = StockPrice::whereDate('date', '<=', $endDate)
            ->orderBy('date', 'desc')
            ->first();


        if (!$startPriceData || !$endPriceData) {
            $missing = [];
            if (!$startPriceData) $missing[] = "data on or before $startDate";
            if (!$endPriceData) $missing[] = "data on or before $endDate";

            return response()->json([
                'error' => 'Not enough data found.',
                'details' => 'Could not find ' . implode(' and ', $missing) . '.'
            ], 404);
        }


        $startPrice = (float)$startPriceData->stock_price;
        $endPrice = (float)$endPriceData->stock_price;

        $valueChange = $endPrice - $startPrice;
        $percentageChange = ($startPrice != 0) ? ($valueChange / $startPrice) * 100 : 0;


        return response()->json([
            'start_period' => [
                'requested_date' => $request->input('start_date'),
                'actual_data_date' => $startPriceData->date,
                'price' => round($startPrice, 4),
            ],
            'end_period' => [
                'requested_date' => $request->input('end_date'),
                'actual_data_date' => $endPriceData->date,
                'price' => round($endPrice, 4),
            ],
            'performance' => [
                'value_change' => round($valueChange, 4),
                'percentage_change' => round($percentageChange, 2) . '%',
            ],
        ]);
    }
}
