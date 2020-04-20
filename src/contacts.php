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
        $query = "select * from (select gender, name, substring_index(origin, ',', 1) as origin, 'firstname' as type from firstname union select null, name, substring_index(origin, ',', 1) as origin, 'lastname' as type from lastname) as names where ";
        foreach ($names as $name) {
          $query .= "lower(name) = '".strtolower($name)."' or ";
        }
        $query = substr($query, 0, -4);
        //$query .= " collate utf8_general_ci";
        $query = $pdo->prepare($query);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $result) {
          $tmp[$result['name']]['count'] += 1;
          $tmp[$result['name']]['type'] = $result['type'];
          $tmp[$result['name']]['origin'] = $result['origin'];
          if($result['type'] == 'firstname') {$tmp[$result['name']]['gender'] = $result['gender'];}
        }
        foreach ($tmp as $key => $value) {
          if($value['count'] == 1) {
            $response[$value['type']]['value'] = $key;
            if($value['type'] == 'firstname') {
              $response[$value['type']]['gender'] = $value['gender'];
              // TODO detect nonunique gender names like Andrea
            }
            if(isset($value['origin'])) {
              $response[$value['type']]['origin'] = $value['origin'];
            }
          }
        }
        foreach ($tmp as $key => $value) {
          if($value['count'] !== 1) {
            if(isset($response['firstname'])) {
              $response['lastname'] = $key;
              if(isset($value['origin'])) {
                $response['firstname']['origin'] = $value['origin'];
                // TODO detect names that can be switched like robert franz
              }
            }
            if(isset($response['lastname'])) {
              $response['firstname']['value'] = $key;
              if($value['type'] == 'firstname') {
                $response['firstname']['gender'] = $value['gender'];
              }
              if(isset($value['origin'])) {
                $response['firstname']['origin'] = $value['origin'];
              }
            }
          }
        }
        echo json_encode($response);
      } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
      }
  }

