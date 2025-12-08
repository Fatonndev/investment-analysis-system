<?php

namespace App\Models;

use App\Database\Database;

class PriceData
{
    private $conn;
    private $table_name = "price_data";

    public $id;
    public $date;
    public $product_type;
    public $size_type;
    public $precision_class;
    public $region;
    public $price;
    public $currency;
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
                    size_type=:size_type,
                    precision_class=:precision_class,
                    region=:region,
                    price=:price,
                    currency=:currency,
                    created_at=NOW(),
                    updated_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->size_type = htmlspecialchars(strip_tags($this->size_type));
        $this->precision_class = htmlspecialchars(strip_tags($this->precision_class));
        $this->region = htmlspecialchars(strip_tags($this->region));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->currency = htmlspecialchars(strip_tags($this->currency));

        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":product_type", $this->product_type);
        $stmt->bindParam(":size_type", $this->size_type);
        $stmt->bindParam(":precision_class", $this->precision_class);
        $stmt->bindParam(":region", $this->region);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":currency", $this->currency);

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

    public function readByProduct($product_type)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE product_type = :product_type 
                  ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_type', $product_type);
        $stmt->execute();
        return $stmt;
    }

    public function readByProductAndRegion($product_type, $region)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE product_type = :product_type AND region = :region
                  ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_type', $product_type);
        $stmt->bindParam(':region', $region);
        $stmt->execute();
        return $stmt;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET 
                    date=:date, 
                    product_type=:product_type, 
                    size_type=:size_type,
                    precision_class=:precision_class,
                    region=:region,
                    price=:price,
                    currency=:currency,
                    updated_at=NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->product_type = htmlspecialchars(strip_tags($this->product_type));
        $this->size_type = htmlspecialchars(strip_tags($this->size_type));
        $this->precision_class = htmlspecialchars(strip_tags($this->precision_class));
        $this->region = htmlspecialchars(strip_tags($this->region));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->currency = htmlspecialchars(strip_tags($this->currency));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":product_type", $this->product_type);
        $stmt->bindParam(":size_type", $this->size_type);
        $stmt->bindParam(":precision_class", $this->precision_class);
        $stmt->bindParam(":region", $this->region);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":currency", $this->currency);

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