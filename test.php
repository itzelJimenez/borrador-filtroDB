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

    public function splitAddress($address, $photographerId, $pdo)
    {
        $response = '';
        // Itera en estados
        foreach ($this->states as $state) {

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
        foreach ($this->cities as $city) {
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
        $url = 'https://public.opendatasoft.com/api/records/1.0/search/?dataset=estados-de-mexico';

        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $estados = curl_exec($ch);
        // Closing
        curl_close($ch);

        //var_dump(json_decode($estados, true));

        $estados = file_get_contents($url);
        // Will dump a beauty json :3
        var_dump(json_decode($estados, true));



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
            $results[] = $normal->splitAddress($item[0], $item[1], $pdo);
        }
        echo "finished\n";

        return $results;
    }
}

$executioner = new Executioner;

print_r($executioner->start());


