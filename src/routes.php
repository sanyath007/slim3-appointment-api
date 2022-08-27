<?php

/** For request options http method */
$app->options('/{routes:.+}', function($request, $response, $args) {
    return $response;
});

/** =============== ROUTES =============== */
$app->get('/', 'HomeController:home')->setName('home');

$app->post('/login', 'LoginController:login')->setName('login');

$app->group('/api', function(Slim\App $app) {
    $app->get('/users', 'UserController:getAll');
    $app->get('/users/{username}', 'UserController:getUser');

    $app->get('/appointments', 'AppointmentController:getAll');
    $app->get('/appointments/{id}', 'AppointmentController:getById');
    $app->get('/appointments/{date}/{type}/count', 'AppointmentController:getCountByDate');
    $app->get('/appointments/{date}/{type}/{clinic}', 'AppointmentController:getCountByClinic');
    $app->get('/appointments/init/form', 'AppointmentController:getInitForm');
    $app->post('/appointments', 'AppointmentController:store');
    $app->put('/appointments/{id}', 'AppointmentController:update');
    $app->delete('/appointments/{id}', 'AppointmentController:delete');
    $app->put('/appointments/{id}/complete', 'AppointmentController:complete');
    $app->put('/appointments/{id}/cancel', 'AppointmentController:cancel');
    $app->put('/appointments/{id}/postpone', 'AppointmentController:postpone');

    $app->get('/postponements', 'PostponementController:getAll');
    $app->get('/postponements/{id}', 'PostponementController:getById');
    $app->get('/postponements/{appointId}/appointment', 'PostponementController:getByAppoint');

    $app->get('/patients', 'PatientController:getAll');
    $app->get('/patients/{id}', 'PatientController:getById');
    $app->get('/patients/{cid}/cid', 'PatientController:getByCid');
    $app->get('/patients/init/form', 'PatientController:getInitForm');
    $app->get('/hpatients/{cid}/cid', 'HPatientController:getByCid');
    $app->get('/hpatients/{hn}/hn', 'HPatientController:getByHn');

    $app->get('/departs', 'DepartmentController:getAll');
    $app->get('/departs/{id}', 'DepartmentController:getById');

    $app->get('/doctors', 'DoctorController:getAll');
    $app->get('/doctors/{id}', 'DoctorController:getById');
    $app->get('/doctors/init/form', 'DoctorController:getInitForm');
    $app->post('/doctors', 'DoctorController:store');
    $app->put('/doctors/{id}', 'DoctorController:update');
    $app->delete('/doctors/{id}', 'DoctorController:delete');
    $app->get('/doctors/{specialist}/clinic', 'DoctorController:getDortorsOfClinic');
    $app->get('/doctors/{id}/schedules', 'DoctorController:getDortorSchedules');

    $app->get('/schedules', 'DoctorScheduleController:getAll');
    $app->get('/schedules/{id}', 'DoctorScheduleController:getById');
    $app->get('/schedules/init/form', 'DoctorScheduleController:getInitForm');
    $app->post('/schedules', 'DoctorScheduleController:store');
    $app->put('/schedules/{id}', 'DoctorScheduleController:update');
    $app->delete('/schedules/{id}', 'DoctorScheduleController:delete');

    $app->get('/dashboard/{month}/stat-card', 'DashboardController:getStatCard');
    $app->get('/dashboard/{month}/appoint-day', 'DashboardController:getAppointPerDay');
    $app->get('/dashboard/{month}/appoint-by-clinic', 'DashboardController:getAppointByClinic');
});
/** =============== ROUTES =============== */

/** use this route if page not found. */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/routes:.+', function ($req, $res) {
    /** using default slim page not found handler. */
    $handler = $this->notFoundHandler;

    return $handler($req, $res);
});
