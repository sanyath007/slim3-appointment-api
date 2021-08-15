<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DiagGroup;
use App\Models\ReferCause;
use App\Models\Right;

class AppointmentController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $appointments = Appointment::with(['patient' => function($q) {
                            $q->select('hn', 'pname', 'fname', 'lname', 'cid', 'tel1');
                        }])
                        ->with(['clinic' => function($q) {
                            $q->select('id', 'clinic');
                        }])
                        ->with(['right' => function($q) {
                            $q->select('id', 'right_name');
                        }])
                        ->get();
        
        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getAppointmentsByPatient($request, $response, $args)
    {
        $page = (int)$request->getQueryParam('page');
        $model = Appointment::where('patient_hn', $args['hn'])
                    ->orderBy('stat_date', 'DESC')
                    ->orderBy('stat_time', 'DESC');
        $appointments = paginate($model, 10, $page, $request);

        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getById($request, $response, $args)
    {
        $appointment    = Appointment::with('right', 'blood_group', 'drug_allergies')
                        ->where('hn', $args['hn'])
                        ->first();
                    
        $data = json_encode($appointment, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'clinics'       => Clinic::all(),
            'diagGroups'    => DiagGroup::all(),
            'referCauses'    => ReferCause::all(),
            'rights'        => Right::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        $this->validator->validate($request, [
            'patient_hn'    => v::numeric(),
            'cid'           => v::numeric(),
            'pname'         => v::numeric(),
            'fname'         => v::numeric(),
            'lname'         => v::numeric(),
            'appoint_date'  => v::stringType()->notEmpty(),
            'appoint_time'  => v::stringType()->notEmpty(),
            'clinic_id'     => v::stringType()->notEmpty(),
            'diag_group'    => v::stringType()->notEmpty(),
            'refer_no'      => v::stringType()->notEmpty(),
            'refer_cause'   => v::stringType()->notEmpty(),
        ]);

        if ($this->validator->failed()) {
            return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 0,
                            'message' => 'Data Invalid !!',
                            'errors' => $this->validator->getMessages()
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }

        try {
            $post = (array)$request->getParsedBody();

            $patient = new Patient;
            $patient->hn            = $post['patient_hn'];
            $patient->cid           = $post['cid'];
            $patient->passport      = $post['passport'];
            $patient->pname         = $post['pname'];
            $patient->fname         = $post['fname'];
            $patient->lname         = $post['lname'];
            $patient->sex           = $post['sex'];
            $patient->birthdate     = $post['birthdate'];
            $patient->tel1          = $post['tel1'];
            $patient->tel2          = $post['tel2'];
            $patient->tel2          = $post['tel2'];
            $patient->right_main    = $post['patient_right'];
            
            if($patient->save()) {
                $appointment = new Appointment;
                $appointment->patient_hn    = $post['patient_hn'];
                $appointment->appoint_date  = $post['appoint_date'];
                $appointment->appoint_time  = $post['appoint_time'];
                $appointment->appoint_type  = $post['appoint_type'];
                $appointment->clinic_id     = $post['clinic_id'];
                $appointment->doctor_id     = $post['doctor_id'];
                $appointment->diag_group    = $post['diag_group'];
                $appointment->diag_text     = $post['diag_text'];
                $appointment->refer_no      = $post['refer_no'];
                $appointment->refer_cause   = $post['refer_cause'];
                $appointment->save();

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'appointment' => $appointment
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status' => 0,
                        'message' => 'Something went wrong!!'
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response
                    ->withStatus(500)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode([
                        'status' => 0,
                        'message' => $ex->getMessage()
                    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
