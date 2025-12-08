<?php

namespace App\Models;

use App\Database\Database;

class CostData
{
    private $conn;
    private $table_name = "cost_data";

    public $id;
    public $date;
    public $cost_type;
    public $amount;
    public $currency;
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET 
                    date=:date, 
                    cost_type=:cost_type, 
                    amount=:amount, 
                    currency=:currency,
                    description=:description,
                    created_at=NOW(),
                    updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->cost_type = htmlspecialchars(strip_tags($this->cost_type));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->currency = htmlspecialchars(strip_tags($this->currency));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":cost_type", $this->cost_type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByDateRange($start_date, $end_date)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE date BETWEEN :start_date AND :end_date 
                  ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function readByType($cost_type)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE cost_type = :cost_type 
                  ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cost_type', $cost_type);
        $stmt->execute();
        return $stmt;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                    date=:date, 
                    cost_type=:cost_type, 
                    amount=:amount, 
                    currency=:currency,
                    description=:description,
                    updated_at=NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->cost_type = htmlspecialchars(strip_tags($this->cost_type));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->currency = htmlspecialchars(strip_tags($this->currency));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":cost_type", $this->cost_type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}