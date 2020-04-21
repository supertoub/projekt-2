<?php
  header("Content-Type:application/json");
  $input_string = $_GET['string'];
  global $response;
  if(isset($input_string)) {
    if(strpos($input_string, '@') !== false) {
      $response['email'] = $input_string;
      $domain = explode('@', $input_string)[1];
      $response['domain'] = $domain;
      $fullname = explode('@', $input_string)[0];
      $fullname = str_replace('.', ' ', $fullname);
      getCompany($domain);
      getName($fullname);
      getPersonDetail($fullname, $domain);
    }
    else {
      getName($input_string);
    }
    echo json_encode($response);
  }

  function getPersonDetail($name, $domain) {
    include 'credentials.php';
    global $response;
    $search = "site:{$domain} \"{$name}\"";
    $search = urlencode($search);
    $about = file_get_contents("https://www.googleapis.com/customsearch/v1?cx=006584671418311382743:jt36an3ix9p&q={$search}&fields=items(link)&key={$googleapikey}");
    $result = json_decode($about);
    if(isset($result->items[0]->link)) {
      $response['more'] = $result->items[0]->link;
    }
  }

  function getCompany($domain) {
    include 'credentials.php';
    global $response;
    try {
      $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password);
      $query = $pdo->prepare("select * from company where website like '%{$domain}%'");
      $query->execute();
      $results = $query->fetchAll(PDO::FETCH_ASSOC);
      if(count($results) == 0) {
        $maps = file_get_contents("https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input={$domain}&inputtype=textquery&fields=name,formatted_address,place_id&key={$googleapikey}");
        $result = json_decode($maps);
        $response['company']['name'] = $result->candidates[0]->name;
        $place_id = $result->candidates[0]->place_id;
        $maps_detail = file_get_contents("https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=international_phone_number,adr_address,website&key={$googleapikey}");
        $result = json_decode($maps_detail);
        $response['company']['phone'] = $result->result->international_phone_number;
        $response['company']['address'] = $result->result->adr_address;
        $response['company']['website'] = $result->result->website;
        $query = "REPLACE INTO company (place_id, companyname, phone, companyaddress, website) VALUES ('{$place_id}', '{$response['company']['name']}', '{$response['company']['phone']}', '{$response['company']['address']}', '{$response['company']['website']}')";
        $query = $pdo->prepare($query);
        $query->execute();
      }
      else {
        $response['company']['name'] = $results[0]['companyname'];
        $response['company']['phone'] = $results[0]['phone'];
        $response['company']['address'] = $results[0]['companyaddress'];
        $response['company']['website'] = $results[0]['website'];
      }
    } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
      }
  }

  function getName($string) {
    include 'credentials.php';
    global $response;
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
                $response['lastname']['origin'] = $value['origin'];
                // TODO detect names that can be switched like robert franz
              }
            }
            if(isset($response['lastname'])) {
              $response['firstname']['value'] = $key;
              $response['firstname']['gender'] = $value['gender'];
              if(isset($value['origin'])) {
                $response['firstname']['origin'] = $value['origin'];
              }
            }
            else {
              $response['name'] = $string;
            }
          }
        }
      } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
      }
  }

