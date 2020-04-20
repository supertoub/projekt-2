<?php
  header("Content-Type:application/json");
  $input_string = $_GET['string'];
  if(strpos($input_string, '@') !== false) {
    $domain = explode('@', $input_string)[1];
    $response['domainname'] = explode('.', $domain)[0];
    $response['toplevel'] = explode('.', $domain)[1];
    $fullname = explode('@', $input_string)[0];
    $response['firstname'] = explode('.', $fullname)[0];
    $response['lastname'] = explode('.', $fullname)[1];
    echo json_encode($response);
  }
  else {
    if($input_string !== '') {
      getName($input_string);
    }
  }

  function getName($string) {
    include_once 'credentials.php';
    try {
        $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password);
        $names = explode(' ', $string);
        $query = "select * from (select gender, name, origin, 'firstname' as type from firstname union select null, name, origin, 'lastname' as type from lastname) as names where ";
        foreach ($names as $name) {
          $query .= "lower(name) = '".strtolower($name)."' or ";
        }
        $query = substr($query, 0, -4);
        //$query .= " collate utf8_general_ci";
        $query = $pdo->prepare($query);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        //$response['firstname'] = $firstname;
        //$response['lastname'] = $lastname;
        //$response['gender'] = $gender;
        echo json_encode($results);
      } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
      }
  }

