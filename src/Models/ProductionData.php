<?php

namespace App\Models;

use App\Database\Database;

class ProductionData
{
    private $conn;
    private $table_name = "production_data";

    public $id;
    public $date;
    public $product_type;
    public $quantity;
    public $unit;
    public $equipment_load;
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
                    product_type=:product_type, 
                    quantity=:quantity, 
                    unit=:unit, 
                    equipment_load=:equipment_load,
                    created_at=NOW(),
                    updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->equipment_load = htmlspecialchars(strip_tags($this->equipment_load));

        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":product_type", $this->product_type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":equipment_load", $this->equipment_load);

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

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                    date=:date, 
                    product_type=:product_type, 
                    quantity=:quantity, 
                    unit=:unit, 
                    equipment_load=:equipment_load,
                    updated_at=NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->equipment_load = htmlspecialchars(strip_tags($this->equipment_load));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":product_type", $this->product_type);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":equipment_load", $this->equipment_load);

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