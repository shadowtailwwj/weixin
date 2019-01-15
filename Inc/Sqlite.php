<?php
class SQLite extends SQLite3
{
    function __construct($file)
    {
        try
        {
            $this->connection=new PDO('sqlite:'.$file);
            //$this->open($file);
        }
        catch(PDOException $e)
        {
            try
            {
                $this->connection=new PDO('sqlite2:'.$file);
            }
            catch(PDOException $e)
            {
                exit('error!');
            }
        }
    }
 
    function __destruct()
    {
        $this->connection=null;
    }

    function Exec($sql) //ֱ������SQL�������ڸ��¡�ɾ������
    {
        return $this->query($sql);
    }

    function query($sql) //ֱ������SQL�������ڸ��¡�ɾ������
    {
        return $this->connection->query($sql);
    }
 
    function getlist($sql) //ȡ�ü�¼�б�
    {
        $recordlist=array();
        foreach($this->query($sql) as $rstmp)
        {
            $recordlist[]=$rstmp;
        }
        return $recordlist;
    }
 
    function Execute($sql)
    {
        return $this->query($sql)->fetch();
    }
 
    function RecordArray($sql)
    {
        return $this->query($sql)->fetchAll();
    }
 
    function RecordCount($sql)
    {
        return count($this->RecordArray($sql));
    }
 
    function RecordLastID()
    {
        return $this->connection->lastInsertId();
    }
}
?>