<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use App\Http\Requests\DestinationRequest;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::orderBy('position')->get();
        return view('destinations.index', compact('destinations'));
    }

    public function store(DestinationRequest $request)
    {
        $position = Destination::max('position') + 1;

        // Assuming you want to handle multiple destinations
        foreach ($request->destinations as $name) {
            Destination::create([
                'destination' => $name,
                'position' => $position,
            ]);

            // Update position for each new destination
            $position++;
        }

        return redirect()->route('destination.create')
                        ->with('success', 'Destination added successfully. <a href="' . route('destination.index') . '">View destination(s)</a>');
    }


    public function create()
    {
        return view('destinations.create');
    }

    public function destroy(Request $request) {
        $destination = Destination::find($request->id);
        $destination->delete();
        return redirect()->route('destination.index')
                        ->with('success', 'Destination deleted successfully. <a href="' . route('destination.create') . '">Add destination</a>');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            $destination = Destination::find($order['id']);
            $destination->position = $order['position'];
            $destination->save();
        }

        return response()->json(['success' => true]);
    }

}
