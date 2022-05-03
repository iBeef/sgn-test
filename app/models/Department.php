<?php

class Department {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get departments assigned to an employee.
     *
     * @access public
     * @param string $rfid
     * @return array
     */
    public function getDepartmentByEmployeeId($id) {
        $sql = "SELECT name
            FROM departments d
            LEFT JOIN employee_department ed
            ON d.id = ed.department_id
            WHERE :employeeId = ed.employee_id
            ORDER BY d.name ASC";
        $this->db->query($sql);
        $this->db->bind('employeeId', $id);
        $results = $this->db->resultSet();
        if(!empty($results)) {
            $results = array_map([$this, 'organiseDepts'], $results);
            return $results;
        } else {
            return [];
        }
    }

    /**
     * Organise the results into a clean array
     *
     * @param Object $sqlObj
     * @return string
     */
    protected function organiseDepts($sqlObj)
    {
        return $sqlObj->name;
    }
}
