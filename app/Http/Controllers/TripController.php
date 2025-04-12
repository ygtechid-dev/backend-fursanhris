<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Trip;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    /**
     * Display a listing of the trips.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Travel')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $user = User::with('employee')->where('id', Auth::user()->id)->first();
        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];

        // Get trips based on role
        $query = Trip::query();

        // For admin, show all trips for the company
        $query->when(Auth::user()->type != 'super admin', function ($q) use ($user) {
            $q->where('created_by', $user->creatorId());
        });

        $trips = $query->with(['company', 'employee'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($trip) use ($companyTz) {
                return $this->formatTripResponse($trip, $companyTz);
            });

        // Count trips by status (you might want to add a status field to your Trip model)
        $tripCounts = [
            'upcoming' => Trip::where('start_date', '>', date('Y-m-d'))
                ->where('created_by', $user->creatorId())
                ->count(),
            'ongoing' => Trip::where('start_date', '<=', date('Y-m-d'))
                ->where('created_by', $user->creatorId())
                ->where('end_date', '>=', date('Y-m-d'))
                ->count(),
            'completed' => Trip::where('end_date', '<', date('Y-m-d'))
                ->where('created_by', $user->creatorId())
                ->count()
        ];

        return response()->json([
            'status' => true,
            'message' => 'Trips retrieved successfully',
            'data' => [
                'trips' => $trips,
                'trip_counts' => $tripCounts,
            ]
        ], 200);
    }

    /**
     * Format the trip response.
     *
     * @param  Trip  $trip
     * @param  string  $timezone
     * @return array
     */
    private function formatTripResponse($trip, $timezone)
    {
        $employee = Employee::find($trip->employee_id);
        $employeeName = $employee ? $employee->name : 'Unknown';

        return [
            'id' => $trip->id,
            'employee_id' => $trip->employee_id,
            'employee_name' => $employeeName,
            'start_date' => $trip->start_date,
            'end_date' => $trip->end_date,
            'purpose_of_visit' => $trip->purpose_of_visit,
            'place_of_visit' => $trip->place_of_visit,
            'description' => $trip->description,
            'created_by' => $trip->created_by,
            'company' => $trip->company,
            'created_at' => $trip->created_at->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'updated_at' => $trip->updated_at->setTimezone($timezone)->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Store a newly created trip in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Travel')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'purpose_of_visit' => 'required|string|max:255',
            'place_of_visit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $trip = Trip::create([
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'purpose_of_visit' => $request->purpose_of_visit,
            'place_of_visit' => $request->place_of_visit,
            'description' => $request->description,
            'created_by' => Auth::user()->creatorId(),
        ]);

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $formattedTrip = $this->formatTripResponse($trip, $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Trip created successfully',
            'data' => $formattedTrip,
        ], 201);
    }

    /**
     * Display the specified trip.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Auth::user()->can('Manage Travel')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $trip = Trip::find($id);

        if (!$trip || $trip->created_by != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Trip not found',
            ], 404);
        }

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $formattedTrip = $this->formatTripResponse($trip, $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Trip retrieved successfully',
            'data' => $formattedTrip,
        ], 200);
    }

    /**
     * Update the specified trip in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('Edit Travel')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $trip = Trip::find($id);

        if (!$trip || $trip->created_by != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Trip not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'sometimes|required|exists:employees,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'purpose_of_visit' => 'sometimes|required|string|max:255',
            'place_of_visit' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $trip->update($request->only([
            'employee_id',
            'start_date',
            'end_date',
            'purpose_of_visit',
            'place_of_visit',
            'description',
        ]));

        $companyTz = Utility::getCompanySchedule(Auth::user()->creatorId())['company_timezone'];
        $formattedTrip = $this->formatTripResponse($trip, $companyTz);

        return response()->json([
            'status' => true,
            'message' => 'Trip updated successfully',
            'data' => $formattedTrip,
        ], 200);
    }

    /**
     * Remove the specified trip from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('Delete Travel')) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied.',
            ], 403);
        }

        $trip = Trip::find($id);

        if (!$trip || $trip->created_by != Auth::user()->creatorId()) {
            return response()->json([
                'status' => false,
                'message' => 'Trip not found',
            ], 404);
        }

        $trip->delete();

        return response()->json([
            'status' => true,
            'message' => 'Trip deleted successfully',
        ], 200);
    }
}
