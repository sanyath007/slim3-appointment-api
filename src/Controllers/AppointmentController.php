<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DiagGroup;
use App\Models\ReferCause;
use App\Models\Right;
use App\Models\Doctor;
use App\Models\Room;

class AppointmentController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $appointments = Appointment::with(['patient' => function($q) {
                            $q->select('id','hn','pname','fname','lname','sex','cid','tel1');
                        }])
                        ->with(['clinic' => function($q) {
                            $q->select('id', 'clinic_name');
                        }])
                        ->with(['diag' => function($q) {
                            $q->select('id', 'name');
                        }])
                        ->with(['right' => function($q) {
                            $q->select('id', 'right_name');
                        }])
                        ->with(['doctor' => function($q) {
                            $q->select('emp_id', 'title', 'license_no');
                        }])
                        ->with(['doctor.employee' => function($q) {
                            $q->select('id', 'prefix', 'fname', 'lname');
                        }])
                        ->orderBy('appoint_date', 'DESC')
                        ->get();
        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $appointment    = Appointment::with(['patient' => function($q) {
                                $q->select('id','hn','pname','fname','lname','cid','tel1','sex','birthdate');
                            }])
                            ->with(['clinic' => function($q) {
                                $q->select('id', 'clinic_name');
                            }])
                            ->with(['diag' => function($q) {
                                $q->select('id', 'name');
                            }])
                            ->with(['right' => function($q) {
                                $q->select('id', 'right_name');
                            }])
                            ->with(['referCause' => function($q) {
                                $q->select('id', 'name');
                            }])
                            ->where('id', $args['id'])
                            ->first();

        $data = json_encode($appointment, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

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

    public function getCountByDate($request, $response, $args)
    {
        $type = $args['type'] == 0 ? false : true;

        $sql = "SELECT appoint_date, COUNT(id) AS num
                FROM appointments ";
        
        if ($type) {
            $sql .= "WHERE (appoint_type='" .$args['type']. "')";
        }
        
        $sql .= "GROUP BY appoint_date ORDER BY appoint_date ";

        $appointments = DB::select($sql);
        $data = json_encode($appointments, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'clinics'       => Clinic::all(),
            'diagGroups'    => DiagGroup::all(),
            'referCauses'   => ReferCause::all(),
            'rights'        => Right::all(),
            'doctors'       => Doctor::with('employee')->get(),
            'rooms'         => Room::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        // $this->validator->validate($request, [
        //     'patient_hn'    => v::numeric(),
        //     'cid'           => v::numeric(),
        //     'pname'         => v::numeric(),
        //     'fname'         => v::numeric(),
        //     'lname'         => v::numeric(),
        //     'appoint_date'  => v::stringType()->notEmpty(),
        //     'appoint_time'  => v::stringType()->notEmpty(),
        //     'clinic_id'     => v::stringType()->notEmpty(),
        //     'diag_group'    => v::stringType()->notEmpty(),
        //     'refer_no'      => v::stringType()->notEmpty(),
        //     'refer_cause'   => v::stringType()->notEmpty(),
        // ]);

        // if ($this->validator->failed()) {
        //     return $response
        //                 ->withStatus(200)
        //                 ->withHeader("Content-Type", "application/json")
        //                 ->write(json_encode([
        //                     'status' => 0,
        //                     'message' => 'Data Invalid !!',
        //                     'errors' => $this->validator->getMessages()
        //                 ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        // }

        // TODO: should check duplicated patient data before store to db
        try {
            $post = (array)$request->getParsedBody();

            if (!empty($post['patient_id'])) {
                $appointment = new Appointment;
                $appointment->patient       = $post['patient_id'];
                $appointment->patient_right = $post['patient_right'];
                $appointment->appoint_date  = thdateToDbdate($post['appoint_date']);
                $appointment->appoint_time  = $post['appoint_time'];
                $appointment->appoint_type  = $post['appoint_type'];
                $appointment->clinic        = $post['clinic'];
                $appointment->doctor        = $post['doctor'];
                $appointment->diag_group    = $post['diag_group'];
                $appointment->diag_text     = $post['diag_text'];
                $appointment->refer_no      = $post['refer_no'];
                $appointment->refer_cause   = $post['refer_cause'];
                $appointment->hospcode      = $post['hospcode'];
                $appointment->appoint_user  = $post['user'];
                $appointment->status        = 0; // 0=?????????????????????????????????, 1=??????????????????????????????, 2=????????????????????????, 3=???????????????????????????
                $appointment->save();

                /** ?????????????????????????????????????????? */
                $this->createAppointForm($appointment->id);

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status' => 1,
                            'message' => 'Inserting successfully',
                            'appointment' => $appointment
                        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }

            $patient = new Patient;
            $patient->hn            = $post['patient_hn'];
            $patient->cid           = $post['cid'];
            $patient->passport      = $post['passport'];
            $patient->pname         = $post['pname'];
            $patient->fname         = $post['fname'];
            $patient->lname         = $post['lname'];
            $patient->sex           = $post['sex'];
            $patient->birthdate     = thdateToDbdate($post['birthdate']);
            $patient->tel1          = $post['tel1'];
            $patient->tel2          = $post['tel2'];
            $patient->main_right    = $post['patient_right'];
            
            if($patient->save()) {
                $appointment = new Appointment;
                $appointment->patient       = $patient->id;
                $appointment->patient_right = $post['patient_right'];
                $appointment->appoint_date  = thdateToDbdate($post['appoint_date']);
                $appointment->appoint_time  = $post['appoint_time'];
                $appointment->appoint_type  = $post['appoint_type'];
                $appointment->clinic        = $post['clinic'];
                $appointment->doctor        = $post['doctor'];
                $appointment->diag_group    = $post['diag_group'];
                $appointment->diag_text     = $post['diag_text'];
                $appointment->refer_no      = $post['refer_no'];
                $appointment->refer_cause   = $post['refer_cause'];
                $appointment->hospcode      = $post['hospcode'];
                $appointment->appoint_user  = $post['user'];
                $appointment->status        = 0; // 0=?????????????????????????????????, 1=??????????????????????????????, 2=????????????????????????, 3=???????????????????????????
                $appointment->save();

                /** ?????????????????????????????????????????? */
                $this->createAppointForm($appointment->id);

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

    public function update($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $appointment = Appointment::find($args['id']);
            $appointment->patient       = $post['patient_id'];
            $appointment->patient_right = $post['patient_right'];
            $appointment->appoint_date  = thdateToDbdate($post['appoint_date']);
            $appointment->appoint_time  = $post['appoint_time'];
            $appointment->appoint_type  = $post['appoint_type'];
            $appointment->clinic        = $post['clinic'];
            $appointment->doctor        = $post['doctor'];
            $appointment->diag_group    = $post['diag_group'];
            $appointment->diag_text     = $post['diag_text'];
            $appointment->refer_no      = $post['refer_no'];
            $appointment->refer_cause   = $post['refer_cause'];
            // $appointment->hospcode      = $post['hospcode'];
            $appointment->appoint_user  = $post['user'];
            // $appointment->status        = 0; // 0=?????????????????????????????????, 1=??????????????????????????????, 2=????????????????????????, 3=???????????????????????????

            if($appointment->save()) {
                /** ?????????????????????????????????????????? */
                $this->createAppointForm($appointment->id);

                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'        => 1,
                            'message'       => 'Updating successfully',
                            'appointment'   => $appointment
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

    public function delete($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $appointment = Appointment::find($args['id']);

            if ($appointment->delete()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'            => 1,
                            'message'           => 'Deleting successfully',
                            'appo$appointment'  => $appointment
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

    public function complete($request, $response, $args)
    {
        try {
            // $post = (array)$request->getParsedBody();
            $appointment = Appointment::find($args['id']);
            $appointment->status = 2; // 0=?????????????????????????????????, 1=??????????????????????????????, 2=????????????????????????, 3=???????????????????????????

            if($appointment->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'        => 1,
                            'message'       => 'Completing successfully',
                            'appointment'   => $appointment
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

    
    public function cancel($request, $response, $args)
    {
        try {
            // $post = (array)$request->getParsedBody();
            $appointment = Appointment::find($args['id']);
            $appointment->status = 3; // 0=?????????????????????????????????, 1=??????????????????????????????, 2=????????????????????????, 3=???????????????????????????

            if($appointment->save()) {
                return $response
                        ->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode([
                            'status'        => 1,
                            'message'       => 'Canceling successfully',
                            'appointment'   => $appointment
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

    private function createAppointForm($id)
    {
        $appointment = Appointment::with(['patient' => function($q) {
                            $q->select('id','hn','pname','fname','lname','cid','tel1','sex','birthdate');
                        }])
                        ->with(['clinic' => function($q) {
                            $q->select('id', 'clinic_name');
                        }])
                        ->with(['clinic.room' => function($q) {
                            $q->select('id', 'room_name', 'room_tel1');
                        }])
                        ->with(['doctor' => function($q) {
                            $q->select('emp_id', 'title', 'license_no');
                        }])
                        ->with(['doctor.employee' => function($q) {
                            $q->select('id', 'prefix', 'fname', 'lname');
                        }])
                        ->with(['diag' => function($q) {
                            $q->select('id', 'name');
                        }])
                        ->with(['right' => function($q) {
                            $q->select('id', 'right_name');
                        }])
                        ->with(['referCause' => function($q) {
                            $q->select('id', 'name');
                        }])
                        ->with(['hosp' => function($q) {
                            $q->select('hospcode', 'name', 'hospital_phone');
                        }])
                        ->where('id', $id)
                        ->first();

        $building = $appointment['relations']['clinic']->id == '9' ? '??????????????? M Park' : '?????????????????????????????????????????????';
        $appointTime = $appointment->appoint_date == '1' ? '08.00 - 12.00 ???.' : '12.00 - 16.00 ???.';
        $doctor = $appointment['relations']['clinic']->id == 9 ? '' : '
                    <p>??????????????? <span>' .$appointment['relations']['doctor']->title.$appointment['relations']['doctor']['relations']['employee']->fname. ' ' .$appointment['relations']['doctor']['relations']['employee']->lname. '</span></p>
                ';
        $before = '';

        if ($appointment['relations']['clinic']->id == 9) {
            $before = '<p><span>-</span></p>';
        } else {
            $before = '
                <div class="checkbox-container">
                <div class="checkmark">
                    <img src="assets/img/checkmark.png" width="20" height="20" />
                </div>
                <div class="checkbox-label">
                    <span>EKG (?????????????????????????????????????????????????????????)</span>
                </div>
            </div>
            <div class="checkbox-container">
                <div class="checkmark">
                    <img src="assets/img/checkmark.png" width="20" height="20" />
                </div>
                <div class="checkbox-label">
                    <span>Chest X-Ray (?????? X-Ray ??????????????????)</span>
                </div>
            </div>
            <div class="checkbox-container">
                <div class="checkmark">
                    <img src="assets/img/checkmark.png" width="20" height="20" />
                </div>
                <div class="checkbox-label">
                    <span>???????????????????????????????????????????????????????????????????????????????????????</span>
                </div>
            </div>
            ';
        }

        $stylesheet = file_get_contents('assets/css/styles.css');
        $content = '
            <div class="container">
                <div class="header">
                    <div class="header-img">
                        <img src="assets/img/logo_mnrh_512x512.jpg" width="100%" height="100" />
                    </div>
                    <div class="header-text">
                        <h1>???????????????????????????' .$appointment['relations']['clinic']->clinic_name. '</h1>
                        <h2>???????????????????????????????????????????????????????????????????????????</h2>
                    </div>
                </div>
                <div class="content">
                    <div class="left__content-container">
                        <div class="left__content-patient">
                            <p>?????????????????????????????????????????? <span>' .$appointment->refer_no. '</span></p>
                            <p>??????????????????????????????????????????????????? <span>' .$appointment['relations']['patient']->cid. '</span></p>
                            <p>????????????-???????????? <span>
                                ' .$appointment['relations']['patient']->pname.$appointment['relations']['patient']->fname. ' '.$appointment['relations']['patient']->lname. '
                            </span></p>
                            <p>???????????????????????? <span>' .$appointment['relations']['patient']->tel1. '</span></p>
                            <p>??????????????????????????????????????? <span>' .$appointment['relations']['right']->right_name. '</span></p>
                            <p>??????????????????????????????????????? <span>' .$appointment['relations']['diag']->name. '</span></p>
                        </div>
                        <div class="left__content-before">
                            <p>????????????????????????????????????????????????</p>
                            ' .$before. '
                        </div>
                    </div>
                    <div class="right__content-container">
                        <div class="right__content-appoint">
                            ' .$doctor. '
                            <p>?????????????????? <span>' .$appointment->appoint_date. '</span></p>
                            <p>???????????? <span>' .$appointTime. '</span></p>
                            </div>
                        <div class="right__content-clinic">
                            <p>???????????????????????????????????? <span>' .$appointment['relations']['clinic']['relations']['room']->room_name. '</span></p>
                            <p><span>' .$building. '</span></p>
                            <p>????????????????????????????????????????????? <span>' .$appointment['relations']['clinic']['relations']['room']->room_tel1. '</span></p>
                        </div>
                        <div class="right__content-remark">
                            <p>???????????????????????? : <span>???????????????????????????????????????????????????????????????????????? ???????????????????????????????????????????????????????????? ???????????????????????????????????????????????????????????????????????????????????????????????????????????????</span></p>
                        </div>
                    </div>
                    <div class="bottom-content">
                        <p>?????????????????????????????????????????????????????????</p>
                        <ul>
                            <li>1. ??????????????????????????? / ???????????????????????? (?????????????????????????????? R9Refer ????????????????????????) <span class="text-underline">?????????' .$appointment['relations']['clinic']['relations']['room']->room_name. '</span></li>
                            <li>2. ????????????????????????????????? ?????????????????????????????????????????????</li>
                            <li>3. ?????????????????????????????????????????????????????????????????????</li>
                            <li>4. ?????????????????????</li>
                            <li>5. ???????????????????????????????????????????????? ????????????????????????????????? ????????? / ???????????? ?????????????????????????????????????????????</li>
                        </ul>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-header">
                        <p>???????????????????????? : <span>???????????????????????????????????????????????????????????????????????? ???????????????????????????????????????????????????????????? ????????????????????????????????????????????????????????????????????????????????????????????????</span></p>
                    </div>
                    <div class="footer-content">
                        <div class="left-footer">
                            <p>???????????????????????????????????? <span>-</span></p>
                            <p>??????????????????????????????????????? <span>-</span></p>
                            <p>?????????/???????????? ???????????????????????? <span>' .$appointment->created_at. '</span></p>
                        </div>
                        <div class="right-footer">
                            <p>???????????????????????????????????????????????????????????????</p>
                            <p><span>' .$appointment['relations']['hosp']->name. '</span></p>
                            <p>???????????????????????? <span>044395000 ????????? 2510</span></p>
                        </div>
                    </div>
                </div>
            </div>
        ';

        // TODO: should create pdf file with generated unduplicate name for avoid browser caching
        $filename = APP_ROOT_DIR . '/public/downloads/' .$appointment->id. '.pdf';

        $this->generatePdf($stylesheet, $content, $filename);
    }

    private function generatePdf($stylesheet, $content, $path)
    {
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                APP_ROOT_DIR . '/public/assets/fonts',
            ]),
            'fontdata' => $fontData + [
                    'sarabun' => [
                        'R' => 'THSarabunNew.ttf',
                        'I' => 'THSarabunNew Italic.ttf',
                        'B' => 'THSarabunNew Bold.ttf',
                    ]
                ],
        ]);

        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output($path, 'F');
    }
}
