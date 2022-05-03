<?php

class Employee {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get employee by RFID card number
     *
     * @access public
     * @param string $rfid
     * @return array
     */
    public function getEmployeeByRfid($rfid) {
        $sql = "SELECT *
            FROM employees e
            WHERE :rfid = e.rfid_card_number
            ORDER BY e.full_name ASC";
        $this->db->query($sql);
        $this->db->bind('rfid', $rfid);
        $result = $this->db->single();
        return $result;
    }
}
