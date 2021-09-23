<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\DoctorSpecialist;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\Specialist;

class DoctorScheduleController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $schedules = DoctorSchedule::with('doctor','doctor.employee')
                    // ->with('employee.position', 'employee.positionClass', 'employee.positionType')
                    // ->with('depart', 'specialists', 'specialists.specialist')
                    ->get();
        
        $data = json_encode($schedules, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getById($request, $response, $args)
    {
        $schedule = DoctorSchedule::where('emp_id', $args['id'])
                    ->with('doctor','doctor.employee')
                    // ->with('employee.position', 'employee.positionClass', 'employee.positionType')
                    // ->with('depart', 'specialists', 'specialists.specialist')
                    ->first();
                    
        $data = json_encode($schedule, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'doctors'       => Doctor::all(),
            'departs'       => Department::all(),
            'clinics'       => Clinic::all(),
            'specialists'   => Specialist::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $schedule = new DoctorSchedule;
            $schedule->doctor           = $post['doctor'];
            $schedule->month            = $post['month'];
            $schedule->period           = $post['period'];
            $schedule->days             = $post['days'];
            $schedule->max_appoint      = $post['max_appoint'];

            if ($schedule->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Inserting successfully',
                            'schedule'  => $schedule
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                        ->withStatus(500)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 0,
                            'message'   => 'Something went wrong!!'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status'    => 0,
                        'message'   => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }

    public function update($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $schedule = DoctorSchedule::find($args['id']);
            $schedule->doctor           = $post['doctor'];
            $schedule->month            = $post['month'];
            $schedule->period           = $post['period'];
            $schedule->days             = $post['days'];
            $schedule->max_appoint      = $post['max_appoint'];

            if ($schedule->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Updating successfully',
                            'schedule'  => $schedule
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                        ->withStatus(500)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 0,
                            'message'   => 'Something went wrong!!'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status'    => 0,
                        'message'   => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }

    public function delete()
    {
        try {
            $schedule = DoctorSchedule::find($args['id']);

            if ($schedule->delete()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 1,
                            'message'   => 'Deleting successfully'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                        ->withStatus(500)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'    => 0,
                            'message'   => 'Something went wrong!!'
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status'    => 0,
                        'message'   => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
