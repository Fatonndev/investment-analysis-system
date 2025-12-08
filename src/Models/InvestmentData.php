<?php

namespace App\Models;

use App\Database\Database;

class InvestmentData
{
    private $conn;
    private $table_name = "investment_data";

    public $id;
    public $investment_name;
    public $investment_type;
    public $amount;
    public $currency;
    public $investment_date;
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
                    investment_name=:investment_name, 
                    investment_type=:investment_type, 
                    amount=:amount, 
                    currency=:currency,
                    investment_date=:investment_date,
                    description=:description,
                    created_at=NOW(),
                    updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->investment_name = htmlspecialchars(strip_tags($this->investment_name));
        $this->investment_type = htmlspecialchars(strip_tags($this->investment_type));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->currency = htmlspecialchars(strip_tags($this->currency));
        $this->investment_date = htmlspecialchars(strip_tags($this->investment_date));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":investment_name", $this->investment_name);
        $stmt->bindParam(":investment_type", $this->investment_type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":investment_date", $this->investment_date);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY investment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByType($investment_type)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE investment_type = :investment_type 
                  ORDER BY investment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':investment_type', $investment_type);
        $stmt->execute();
        return $stmt;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                    investment_name=:investment_name, 
                    investment_type=:investment_type, 
                    amount=:amount, 
                    currency=:currency,
                    investment_date=:investment_date,
                    description=:description,
                    updated_at=NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->investment_name = htmlspecialchars(strip_tags($this->investment_name));
        $this->investment_type = htmlspecialchars(strip_tags($this->investment_type));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->currency = htmlspecialchars(strip_tags($this->currency));
        $this->investment_date = htmlspecialchars(strip_tags($this->investment_date));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(":investment_name", $this->investment_name);
        $stmt->bindParam(":investment_type", $this->investment_type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":investment_date", $this->investment_date);
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