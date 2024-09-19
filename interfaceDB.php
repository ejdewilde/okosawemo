<?php 

    class FFDb
        {

        public $db_local;
        public $db_mysqli_local;
        public $utf8_connect=FALSE;

        public function __CONSTRUCT($test=false)
            {
                //echo "IN DE DB!<BR>";
                if ($test){
                    echo "<h2>Testing DB Connection</h2>";
                    $this->connect();
                    echo "connected to: {$_SERVER["SERVER_NAME"]}"; 
                }
            }

        public function closedb()
            {
                mysqli_close($this->db_mysqli_local);
            }

        // determine locale and connect to DB
        public function connect(){    
            // local
            if  ($_SERVER["SERVER_NAME"]=="localhost") 
                {
                    $this->db_mysqli_local=mysqli_connect('localhost', 'user', 'user', 'oko');
                }
                  // connect user on server        
            else
            
                {
                $this->db_mysqli_local=mysqli_connect('192.168.137.189', 'tbweb10026', 'HcOtYaU>bFiBJ5gFV-APNeMA', 'tbweb10026');
                }

            // error? kill script
            if ($this->db_mysqli_local->connect_errno)
            {
                die('Connect Error: ' . $this->db_mysqli_local->connect_errno);
            }

                                  
         if ($this->utf8_connect)
            {
                $this->db_mysqli_local->set_charset("utf8");
            }

        }

        // lost leesteken problemen op binnen sql/php, maar creeert problemen in drillkaarten
        public function UTF8_Connect_Set($val)
                {
                    $this->utf8_connect=$val;
                }

        // execute query 
        // return result or false
        // TO BE ADDED TO
        public function exesql($sql)
            {
                $mysqli=$this->connect();
                if (is_string($sql))
                    {
                        
                        $output= $this->db_mysqli_local->query($sql);
                    }
                else 
                    {
                        $output= false;
                    }
                $this->closedb();
                return $output;
            }      

        public function getArray($sql)
            {
                //echo "hallo";
                $output=array();
                $this->connect();
                //  print_r($sql);   
                $result=$this->exesql($sql);
                if ($result->num_rows > 0)
                 {
                    // output data of each row
                    while($row = $result->fetch_assoc()) {
                        $output[]=$row;
                        }
               
                return $output;
                }
            }
   
 
        // return array with string; use when select is for one item only. ie: select 'gemeente' from 'regio'
        // WATCH OUT: DISTINCT
        public function getStrings($field, $table, $selector="")
            {
                $output=array();
                $sql="SELECT distinct($field) FROM $table $selector";
                //echo $sql."<br>";
                $this->connect();  
                $result=$this->exesql($sql);
                if ($result->num_rows > 0)
                 {
                    while($row = $result->fetch_assoc()) {
                        $output[]=$row[$field];
                        }
                 }
                return $output;
                 }


            public function getStringsPretty($sql, $field)
            {
                $output=array();
                $this->connect();  
                $result=$this->exesql($sql);
                if ($result->num_rows > 0)
                 {
                    // output data of each row
                    while($row = $result->fetch_assoc()) {
                        $output[]=$row[$field];
                        }
                 }
                return $output;
                 }

           // return single string from query
        public function getString($sql)
            {
                $result=$this->exeSQL($sql);
                if ($result->num_rows > 0)
                    {
                        //return ( array_values($result->fetch_assoc())[0]);
                        $thisa=$result->fetch_assoc();
                        $tor =array_values($thisa);
                        return $tor[0];
                    }
            }


            // return 2 values as associative array kop=>val
        public function getStringsAArray($sql, $kop, $val)
                {
                    $output=array();
                    $this->connect();  
                    $result=$this->exesql($sql);
                    if ($result->num_rows > 0)
                        {
                            // output data of each row
                            while($row = $result->fetch_assoc()) 
                                {
                                 $output[$row[$kop]]=$row[$val];
                                }
                        }
                return $output;
                 }

            public function getStringsFromArray($sql)
                {
                $output=array();
                $result=array_values($this->getArray($sql));
                foreach ($result as $val)
                    {
                        $output[]=array_values($val);
                        
                    }
                return $output;
                 }

            public function getStringsAsLump($sql)
                {
                $output=array();
                $result=array_values($this->getArray($sql));
                //print_r($result);
                foreach ($result as $val)
                    {
                      //  print_r($val);
                     //   echo "$<br>";
                        $output[]=array_values($val);
                        
                    }
                return $output;
                 }

        } // end class Toegang
    
//$t=new InterfaceDB();
//$t->getStringsFromArray("select * from AOJ_ouders");
?>