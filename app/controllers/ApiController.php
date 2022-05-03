<?php

class ApiController extends Controller {

    private $employeeModel;

    public function __construct() {
        $this->employeeModel = $this->model('Employee');
        $this->departmentModel = $this->model('Department');
    }

    /**
     * Not found route
     *
     * @return json
     */
    public function error404()
    {
        $response = [
            'msg' => 'Route not found'
        ];
        sendJson($response, 404);
    }

    public function getEmployee()
    {
        $invalidResponse = [
            "full_name" => '',
            'department' => []
        ];
        $validator = new Validator();
        $validator->sanitizeInput('GET');
        $validator->method('get')
            ->value('cn')
            ->pattern('alphaNumeric')
            ->isRequired();
            
        if(!$validator->isValid()) {
            sendJson($invalidResponse, 404);
        } else {
            $input = $validator->getValidInputs();
            $employee = $this->employeeModel
                ->getEmployeeByRfid($input['cn']);
            if(!empty($employee)) {
                $departments = $this->departmentModel
                    ->getDepartmentByEmployeeId($employee->id);
            } else {
                sendJson($invalidResponse, 404);
            }
            sendJson([
                'full_name' => $employee->full_name,
                'department' => $departments
            ]);
        }
    }
}