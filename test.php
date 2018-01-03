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
        'Ciudad Victoria',
        'Tlaxcala de Xicohténcatl',
        'Xalapa-Enríquez',
        'San Miguel de Cozumel',
        'Veracruz',
        'Mérida',
        'Zacatecas'

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
        'México',
        'Mexico',
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
        'Tlaxcala',
        'Veracruz',
        'Yucatán',
        'Yucatan',
        'Zacatecas'
    ];

    public function splitAddress($address)
    {
        $response = '';

        // Itera en estados
        foreach ($this->states as $state) {
            $pos = strpos($address, $state);
            if ($pos) {
                $response .= "ESTADO: $state /";
                // se guarda el state en BD - TODO
            } /*else {
                $response .= "$state NO encontrado\n";
            }*/
        }

        // Itera en ciudades
        foreach ($this->cities as $city) {
            $pos = strpos($address, $city);
            if ($pos) {
                 $response .= "CIUDAD: $city";
                // se guarda el city en BD - TODO
            } /*else {
                $response .= "$city NO encontrada\n";
            }*/
        }

        return $response;
    }
}

class Executioner {

    public function start()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=fotec','root', 'root');
        $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $pdo->exec("SET CHARACTER SET utf8"); 
        $sql = "select users.address,  photographers.city, photographers.state from users
        inner join role_user on  role_user.user_id = users.id
        inner join photographers on photographers.user_id = users.id
        where role_user.role_id = 4
        and users.address is not null
        and photographers.city = ''
        or photographers.state = ''";
        $data = $pdo->query($sql);
        /*
        $rows = $data->fetchAll();
        foreach ($data as $dat) {
            var_dump($dat);
        }
        */

        /*
        select users.address,  photographers.city, photographers.state from users
        inner join role_user on  role_user.user_id = users.id
        inner join photographers on photographers.user_id = users.id
        where role_user.role_id = 4
        and users.address is not null
        and photographers.city = ''
        or photographers.state = ''
        */


        $results = [];
        $normal = new NormalizeInfo;

        // Obtener las direcciones de DB que no tienen el campo city y state con info y guardarla en data

        echo "started\n";
        foreach($data as $address) {
            //results[] = $normal->splitAddress(strtolower ($address[0]));
            //$results[] = $address[0];
            var_dump(strtolower ($address[0]));
        }
        echo "finished\n";

        return $results;
    }
}

$executioner = new Executioner;

print_r($executioner->start());


