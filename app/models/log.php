<?php
// app/Models/Log.php
class LogModel {
    private $db;
    public function __construct($db){ $this->db=$db; }
    public function countType($type,$from=null,$to=null){
        $sql="SELECT COUNT(*) FROM user_logs WHERE tipe=?";
        $params=[$type];
        if($from){ $sql.=" AND created_at >= ?"; $params[]=$from; }
        if($to){ $sql.=" AND created_at <= ?"; $params[]=$to; }
        $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchColumn();
    }
}
