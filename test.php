<?php

class NormalizeInfo {


    protected $cities = [
        'Aguascalientes',
        'Mexicali',
        'Tijuana',
        'La paz',
        'San Francisco de Campeche',
        'Tuxtla Gutierrez',
        'Tuxtla Gutiérrez',
        'Chihuahua',
        'Ciudad Juárez',
        'Ciudad Juarez',
        'Ciudad de Mexico',
        'Ciudad de México',
        'CDMX',
        'Saltillo',
        'Colima',
        'Victoria de Durango',
        'Guanajuato',
        'Gto',
        'San Miguel de Allende',
        'Leon de Aldama',
        'León de Aldama',
        'Chilpancingo de los Bravo',
        'Acapulco de Juárez',
        'Acapulco',
        'Pachuca de Soto',
        'Guadalajara',
        'Toluca de Lerdo',
        'Toluca',
        'Ecatepec de Morelos',
        'Ecatepec',
        'Morelia',
        'Cuernavaca',
        'Tepic',
        'Monterrey',
        'Oaxaca de Juárez',
        'Oaxaca',
        'Puebla',
        'Puebla de Zaragoza',
        'Santiago de Querétaro',
        'Santiago de Queretaro',
        'Chetumal',
        'San Luis Potosí',
        'S.L.P.',
        'SLP',
        'Culiacán Rosales',
        'Culiacán',
        'Hermosillo',
        'Villahermosa',
        'Reynosa',
        'Tampico',
        'Ciudad Victoria',
        'Tlaxcala de Xicohténcatl',
        'Torreón,',
        'Xalapa-Enríquez',
        'San Miguel de Cozumel',
        'Veracruz',
        'Mérida',
        'Zacatecas',
        'Nezahualcóyotl'

    ];

    protected $states = [
        'Aguascalientes',
        'Ags',
        'Baja California',
        'BC',
        'B.C.',
        'Baja California Sur',
        'BCS',
        'B.C.S.',
        'Campeche',
        'Camp',
        'Chiapas',
        'Chis',
        'Chih',
        'Chihuahua',
        'Ciudad de México',
        'Ciudad de Mexico',
        'Mexico city',
        'CDMX',
        'Coahuila',
        'Colima',
        'Durango',
        'Dgo',
        'Guanajuato',
        'Gto',
        'Guerrero',
        'Gro',
        'Hidalgo',
        'Hgo',
        'Jalisco',
        'Jal',
        'Estado de México',
        'Estado de Mexico',
        'Michoacán',
        'Michoacan',
        'MICH',
        'Morelos',
        'Nayarit',
        'Nuevo León',
        'Nuevo Leon',
        'NL',
        'N.L.',
        'Oaxaca',
        'Puebla',
        'Querétaro',
        'Queretaro',
        'Qro',
        'Quintana Roo',
        'QR',
        'San Luis Potosí',
        'SLP',
        'S.L.P.',
        'Sinaloa',
        'Sonora',
        'Tabasco',
        'Tamaulipas',
        'tamaulipas',
        'Tlaxcala',
        'Veracruz',
        'Yucatán',
        'Yucatan',
        'Zacatecas'
    ];

    public function splitAddress($address, $photographerId, $pdo, $arrayStates, $arrayCities)
    {
        $response = '';
        // Itera en estados
        foreach ($arrayStates as $state) {

            $pos = stripos($address, $state);
            if ($pos) {
                $response .= "ESTADO: $state /";
                
                $update = "UPDATE `photographers` SET `state` = (:state) WHERE `id` = (:id)";
                $statement = $pdo->prepare($update);
                $statement->bindValue(':id', $photographerId,  PDO::PARAM_STR);
                $statement->bindValue(':state', $state,  PDO::PARAM_STR);
                
                $inserted = $statement->execute();
                if($inserted){
                    echo 'State Success';
                }
                // se guarda el state en BD - TODO
            } else {
                //x$response .= "ESTADO: $state NO encontrado\n";
            }
        }

        // Itera en ciudades
        foreach ($arrayCities as $city) {
            $pos = strpos($address, $city);
            if ($pos) {
                $response .= "CIUDAD: $city";
                $update = "UPDATE `photographers` SET `city` = (:city) WHERE `id` = (:id)";
                $statement = $pdo->prepare($update);
                $statement->bindValue(':id', $photographerId,  PDO::PARAM_STR);
                $statement->bindValue(':city', $city,  PDO::PARAM_STR);
                
                $inserted = $statement->execute();
                if($inserted){
                    echo 'City Success';
                }
            } else {
                //$response .= "CIUDAD: $city NO encontrada\n";
            }
        }

        return $response;
    }
}

class Executioner {

    public function start()
    {
        $urlStates = 'https://public.opendatasoft.com/api/records/1.0/search/?dataset=estados-de-mexico&rows=32';
        $urlCities = 'https://public.opendatasoft.com/api/records/1.0/search/?dataset=ciudades-de-mexico&rows=1853&facet=name_1&facet=name_2';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlStates);
        curl_setopt($ch, CURLOPT_URL,$urlCities);
        $states = curl_exec($ch);
        $cities = curl_exec($ch);
        curl_close($ch);

        $states = file_get_contents($urlStates);
        $resStates = json_decode($states, true);
        $states = $resStates['records'];
        $arrayStates = array(); 

        $cities = file_get_contents($urlCities);
        $resCities = json_decode($cities, true);
        $cities = $resCities['records'];
        $arrayCities = array(); 

        foreach ($states as $state) {
            array_push($arrayStates, ucwords($state['fields']['estado']));
            array_push($arrayStates, quitar_tildes($state['fields']['estado']));
        }

        array_push($arrayStates, 'Estado De México');
        array_push($arrayStates, 'Ciudad De México');

        foreach ($cities as $city) {
            array_push($arrayCities, ucwords($city['fields']['name_2']));
        }

        array_push($arrayCities, 'Ciudad De México');

        $pdo = new PDO('mysql:host=localhost;dbname=fotec','root', 'root');
        $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET CHARACTER SET utf8"); 

        // Obtener las direcciones de DB que no tienen el campo city y state con info y guardarla en data
        $sql = "select users.address,  photographers.id from users
        inner join role_user on  role_user.user_id = users.id
        inner join photographers on photographers.user_id = users.id
        where role_user.role_id = 4
        and users.address is not null
        and photographers.city = ''
        or photographers.state = ''";
        $data = $pdo->query($sql);
        $results = [];
        $normal = new NormalizeInfo;

        echo "started\n";

        foreach($data as $item) {
            $results[] = $normal->splitAddress(ucwords(strtolower($item[0])), $item[1], $pdo, $arrayStates, $arrayCities);
        }

        echo "finished\n";

        return $results;
    }
}

$executioner = new Executioner;

print_r($executioner->start());


