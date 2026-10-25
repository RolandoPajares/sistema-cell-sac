<?php

class Model extends Conexion
{

    protected $table;
    protected $primaryKey = "id";

    protected $select = "*";
    protected $joins = [];
    protected $whereBuilder = [];
    protected $orderBy = "";
    protected $limit = "";

    public function __construct()
    {
        parent::__construct();
    }

    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->connect()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        if ($stmt->execute()) {
            return $this->connect()->lastInsertId();
        }
        return false;
    }

    public function update($id, $data)
    {
        $setClause = "";
        foreach ($data as $key => $value) {
            $setClause .= "{$key} = :{$key}, ";
        }
        $setClause = rtrim($setClause, ", ");
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connect()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY BUILDER
    |--------------------------------------------------------------------------
    */
    public function select($fields)
    {
        $this->select = $fields;
        return $this;
    }

    public function where($conditions)
    {
        foreach ($conditions as $condition) {
            $this->whereBuilder[] = $condition;
        }
        return $this;
    }

    public function orderBy($field, $direction = "ASC")
    {
        $this->orderBy = "ORDER BY {$field} {$direction}";
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = "LIMIT {$limit}";
        return $this;
    }

    public function get()
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }
        if (!empty($this->whereBuilder)) {
            $sql .= " WHERE " . implode(" AND ", $this->whereBuilder);
        }
        if (!empty($this->orderBy)) {
            $sql .= " " . $this->orderBy;
        }
        if (!empty($this->limit)) {
            $sql .= " " . $this->limit;
        }
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        $this->resetQuery();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first()
    {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }

    private function resetQuery()
    {
        $this->select = "*";
        $this->joins = [];
        $this->whereBuilder = [];
        $this->orderBy = "";
        $this->limit = "";
    }
}
