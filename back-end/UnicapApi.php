<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

Class UnicapApi {
    private $matricula;
    private $password;
    private $cookie;
    private $name;

    public function login($matricula,$password){
        $this->matricula = $matricula;
        $this->password = $password;

        $data = http_build_query(
            array(
                'flag' => 'index.php',
                'login' => $matricula,
                'password' => $password,
                'button' => "Access"
            )
        );
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $result = $this->request("https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/php/login_usu.php",$data,$header,"POST");

        $getHeaders = $this->parseHeaders($result->headers);
        if (preg_match("/matr.*cula.*inv.*lida/i",$result->response,$match) || preg_match("/senhaa.*inv.*lida/i",$result->response,$match)  ){
            echo "0";
            return 0;
        }
        $location = $getHeaders["Location"];
        $name = $location;
        $this->cookie = $getHeaders["Set-Cookie"];
        $header = array ('Content-Type: application/x-www-form-urlencoded','Referer: https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/php/login_usu.php','Cookie: '.$this->cookie);
        $result = $this->request('https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/php/'.$location,"",$header,"GET");
        $getHeaders = $this->parseHeaders($result->headers);
        $location = $getHeaders["Location"];
        
        $result = $this->request("https://www1.unicap.br//pergamum3/Pergamum/biblioteca_s/php/$location","",$header,"GET");

        $this->name = preg_replace("/\+/"," ",preg_replace("/&.*/","",preg_replace("/.*\?.*?=/","", $name)));
        if (preg_match("/$this->name/i", $result->response, $match)){
            echo $this->cookie;
            return $this->cookie;
        }
        else{
            echo "0";
            return 0;
        }
    }

    public function getImage($matricula){
        $header = array('Content-Type: application/x-www-form-urlencoded');
        $result = $this->request("https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/getImg.php?cod_pessoa=$matricula","",$header,"GET");
        header('Content-Type: image/jpeg');
        print_r($result->response);
    }

    public function getLivros($cookie){
        
    }

    private function request($url,$data,$headers,$type){

        $header = array('http' =>
            array(
                'method'  => $type,
                'header'  => $headers,
                'content' => $data
            )
        );

        $context  = stream_context_create($header);
        $response = file_get_contents($url, false, $context);
        
        $result->response = $response;
        $result->headers = $http_response_header;
        return $result;
    }

    private function parseHeaders( $headers ){
        $head = array();
        foreach( $headers as $k=>$v )
        {
            $t = explode( ':', $v, 2 );
            if( isset( $t[1] ) )
                $head[ trim($t[0]) ] = trim( $t[1] );
            else
            {
                $head[] = $v;
                if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                    $head['reponse_code'] = intval($out[1]);
            }
        }
        return $head;
    }
}
?>