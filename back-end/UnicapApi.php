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
        $this->cookie = $cookie;
        $header = array ('Content-Type: application/x-www-form-urlencoded','Referer: https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/emp_renovacao.php','Cookie: '.$this->cookie);
        $result = $this->request("https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/emp_renovacao.php","",$header,"GET");
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($result->response, 'HTML-ENTITIES', 'UTF-8'));
        $tds = $dom->getElementsByTagName('td');
        $cod_livros = array();
        $livros = array();
        $data_entrega = array();
        foreach ($tds as $td) {
            if ($td->getAttribute('class') == "box_write_left"){
                array_push($cod_livros,$td->nodeValue);
            }
        }
        array_shift($cod_livros);
        
        foreach ($tds as $td) {
            if ($td->getAttribute('class') == "box_azul_left"){
                array_push($livros,$td->nodeValue);
            }
        }
        array_shift($livros);
        
        foreach ($tds as $td) {
            if ($td->getAttribute('class') == "box_write_c"){
                array_push($data_entrega,$td->nodeValue);
            }
        }
        array_shift($data_entrega);

        $cod_livros_data = array();
        for($i=0;$i<count($cod_livros);$i++){
            $aux = array(
                "id" => $cod_livros[$i],
                "livro" => $livros[$i],
                "data" => $data_entrega[$i]
            );
            array_push($cod_livros_data,$aux);
        }
        return json_encode($cod_livros_data);
    }

    public function renovaLivrosByCod($cod,$matricula,$cookie){

        $this->cookie = $cookie;
        $header = array ('Content-Type: application/x-www-form-urlencoded','Referer: https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/emp_renovacao.php','Cookie: '.$this->cookie);

        $data = http_build_query(
            array(
                'renova' => 'renovar',
                'Selecs' => "$cod@#1@#1;",
                'acao' => 'clicou',
                'codigoreduzido_anterior' => "$matricula",
                'check_1' => "$cod@#1@#1;",
                'data_pesquisa_inicial' => "dd%2Fmm%2Faaaa",
                'data_pesquisa_final' => "dd%2Fmm%2Faaaa"

            )
        );
        $result = $this->request("https://www1.unicap.br/pergamum3/Pergamum/biblioteca_s/meu_pergamum/emp_renovacao.php",$data,$header,"POST");
        print_r($result->response);

    }

    private function getTextBetweenTags($string, $tagname){
        $pattern = "/<$tagname?.*>(.*)<\/$tagname>/";
        preg_match($pattern, $string, $matches);
        return $matches;
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