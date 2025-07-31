<?php
/**
 * Script para insertar las 100 preguntas del test de tránsito
 * Ejecutar después de install.php
 */

// Configuración
$config = [
    'db_host' => 'localhost',
    'db_name' => 'test_transito',
    'db_user' => 'root',
    'db_pass' => ''
];

echo "=== INSERTAR PREGUNTAS ===\n\n";

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", 
                    $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexión exitosa\n\n";

    // Datos de las preguntas
    $questions = [
        ['ID' => 1, 'NRO' => 10, 'TEXTO' => 'La señal que realiza el agente de la autoridad, mediante un toque largo de silbato indica: ', 'ARTINC' => '6310', 'RESP1' => 'Que los vehículos pueden continuar la marcha.', 'RESP2' => 'La aproximación a una interrupción en la vía resultando necesario disminuir la velocidad.', 'RESP3' => 'Que los vehículos deben detenerse.', 'CORRECTA' => 1, 'TEMATICA' => 1],
        ['ID' => 2, 'NRO' => 825, 'TEXTO' => '¿Qué deben hacer los conductores que se acerquen a un agente de circulación que tiene el brazo levantado verticalmente? ', 'ARTINC' => '6301', 'RESP1' => 'Reanudar la marcha. ', 'RESP2' => 'Disminuir la velocidad.', 'RESP3' => 'Detenerse de inmediato.', 'CORRECTA' => 3, 'TEMATICA' => 1],
        ['ID' => 3, 'NRO' => 844, 'TEXTO' => 'Si usted ingiere bebida alcohólica, que tipo de vehículo está prohibido conducir. ', 'ARTINC' => '95', 'RESP1' => 'Los de uso personal.', 'RESP2' => 'Ninguno.', 'RESP3' => 'Los del sector estatal.', 'CORRECTA' => 3, 'TEMATICA' => 1],
        ['ID' => 4, 'NRO' => 853, 'TEXTO' => 'Los pasajeros están obligados a: ', 'ARTINC' => '148', 'RESP1' => 'Permanecer sentados en vehículos con barandas inferiores a 120 centímetros.', 'RESP2' => 'Descender o abordar con el vehículo detenido y hacerlo por cualquier lado de la vía.', 'RESP3' => 'Llevar parte del cuerpo u objetos fuera de la cabina o cama.', 'CORRECTA' => 1, 'TEMATICA' => 1],
        ['ID' => 5, 'NRO' => 880, 'TEXTO' => '¿Qué beneficios ofrece la notificación preventiva? ', 'ARTINC' => '0', 'RESP1' => 'Es una medida profiláctica y educativa.', 'RESP2' => 'Se paga la mitad del importe.', 'RESP3' => 'Se aplica solo a los conductores novel.', 'CORRECTA' => 1, 'TEMATICA' => 1],
        ['ID' => 6, 'NRO' => 835, 'TEXTO' => '¿En qué vías es obligatorio que el conductor de La motocicleta lleve puesto el casco de protección? ', 'ARTINC' => '7201', 'RESP1' => 'En toda clase de vías.', 'RESP2' => 'Sólo en vías urbanas.', 'RESP3' => 'En cualquier vía, salvo en las calles residenciales.', 'CORRECTA' => 1, 'TEMATICA' => 2],
        ['ID' => 7, 'NRO' => 845, 'TEXTO' => 'En una intersección, ¿debe detenerse ante una señal vertical de PARE, si un Agente le indica claramente que puede pasar?', 'ARTINC' => '62', 'RESP1' => 'Sí, por precaución.', 'RESP2' => 'No, pero pasaré extremando las precauciones y cediendo el paso a los vehículos que se acerquen por mi derecha.', 'RESP3' => 'No, porque debo atender al mandato emitido por el Agente.', 'CORRECTA' => 3, 'TEMATICA' => 2],
        ['ID' => 8, 'NRO' => 850, 'TEXTO' => '¿Se prohíbe ingerir bebidas alcohólicas en los vehículos y su transportación en los compartimientos destinados al conductor y a los pasajeros cuando es evidente que se está consumiendo? ', 'ARTINC' => '95', 'RESP1' => 'Si.', 'RESP2' => 'No.', 'RESP3' => 'A veces.', 'CORRECTA' => 1, 'TEMATICA' => 2],
        ['ID' => 9, 'NRO' => 852, 'TEXTO' => 'Los pasajeros están obligados a: ', 'ARTINC' => '148', 'RESP1' => 'Hacer uso de los medios de protección pasivos instalados en el vehículo.', 'RESP2' => 'Llevar parte del cuerpo u objetos fuera de la cabina o cama.', 'RESP3' => 'Descender o abordar con el vehículo detenido y hacerlo por cualquier lado de la vía.', 'CORRECTA' => 1, 'TEMATICA' => 2],
        ['ID' => 10, 'NRO' => 875, 'TEXTO' => 'El Ministerio del Interior bonificará al conductor que efectúa el pago de las multas en las oficinas habilitadas para ese fin dentro de los: ', 'ARTINC' => '0', 'RESP1' => 'Tres días hábiles siguientes a la notificación de la infracción.', 'RESP2' => 'Diez días naturales siguientes a la notificación de la infracción.', 'RESP3' => 'Tres días naturales siguientes a la notificación de la infracción.', 'CORRECTA' => 1, 'TEMATICA' => 2],
        // Continuar con las 90 preguntas restantes...
        ['ID' => 11, 'NRO' => 81, 'TEXTO' => 'Al aproximarse a un paso a nivel con barreras u otras señales sonoras o lumínicas, el conductor de cualquier vehículo está obligado a:', 'ARTINC' => '8002', 'RESP1' => 'Moderar la velocidad y tomar precauciones.', 'RESP2' => 'Detener la marcha.', 'RESP3' => 'Ceder el paso. ', 'CORRECTA' => 1, 'TEMATICA' => 3],
        ['ID' => 12, 'NRO' => 86, 'TEXTO' => 'Como conductor de cualquier vehículo, al observar peatones que empiecen a cruzar o se encuentren cruzando la calzada por vías con las marcas tipo cebras, usted está obligado a:', 'ARTINC' => '8200', 'RESP1' => 'Disminuir velocidad.', 'RESP2' => 'Accionar el claxon o aparato similar.', 'RESP3' => 'Detener el vehículo y ceder el paso.', 'CORRECTA' => 3, 'TEMATICA' => 3],
        ['ID' => 13, 'NRO' => 75, 'TEXTO' => 'Usted, en su condición de conductor de cualquier vehículo de motor debe tener en cuenta que las vías que no tengan señalizado su sentido de dirección se consideran de:', 'ARTINC' => '7900', 'RESP1' => 'Doble sentido de circulación.', 'RESP2' => 'Un solo sentido de circulación.', 'RESP3' => 'Es un error de señalización.', 'CORRECTA' => 1, 'TEMATICA' => 3],
        ['ID' => 14, 'NRO' => 829, 'TEXTO' => '¿Qué debe hacer si al aproximarse a un paso a nivel que no tenga señalización que establezca la conducta a seguir? ', 'ARTINC' => '8001', 'RESP1' => 'Detenerme siempre.', 'RESP2' => 'Detenerme únicamente si observo que no me va a dar tiempo a cruzar el paso sin peligro. ', 'RESP3' => 'Cruzar el paso a nivel si observo que el vehículo sobre raíles ya ha cruzado.', 'CORRECTA' => 1, 'TEMATICA' => 3],
        ['ID' => 15, 'NRO' => 851, 'TEXTO' => 'La inmovilización del vehículo finaliza cuando: ', 'ARTINC' => '97', 'RESP1' => 'El conductor sea sustituido por otro habilitado, que ofrece garantías suficientes.', 'RESP2' => 'No ha desaparecido la causa que lo motivó.', 'RESP3' => 'Esté habilitado judicial o administrativamente para conducir vehículos.', 'CORRECTA' => 1, 'TEMATICA' => 3],
        ['ID' => 16, 'NRO' => 811, 'TEXTO' => 'Según lo que observa en la imagen. ¿Está prohibido adelantar detrás del camión pipa que realiza la misma maniobra y no permite la visibilidad de la parte delantera de la vía destinada al sentido contrario?', 'ARTINC' => '8501', 'RESP1' => 'No, porque me sirve de protección.', 'RESP2' => 'Sí, porque el camión no permite la visibilidad de la parte delantera de la vía destinada al sentido contrario.', 'RESP3' => 'No, si se circula lentamente.', 'CORRECTA' => 2, 'TEMATICA' => 4],
        ['ID' => 17, 'NRO' => 129, 'TEXTO' => '¿Cuándo el conductor de cualquier vehículo puede realizar la maniobra de marcha atrás?', 'ARTINC' => '9202', 'RESP1' => 'A una distancia 30 metros.', 'RESP2' => 'Obligue a otro vehículo a realizar un cambio en su dirección o velocidad.', 'RESP3' => 'La velocidad no exceda los 20 kilómetros por hora.', 'CORRECTA' => 3, 'TEMATICA' => 4],
        ['ID' => 18, 'NRO' => 34, 'TEXTO' => 'Transitando por una vía de dos carriles y dos sentidos de dirección, usted adelantará a la moto que marcha delante:', 'ARTINC' => '8502', 'RESP1' => 'Por el carril derecho, realizando las señales correspondientes de brazo.', 'RESP2' => 'Por la derecha, usando el paseo.', 'RESP3' => 'Por el carril de la izquierda, cumpliendo las exigencias establecidas para el adelantamiento.', 'CORRECTA' => 3, 'TEMATICA' => 4],
        ['ID' => 19, 'NRO' => 33, 'TEXTO' => '¿Cuándo se puede realizar una maniobra de marcha atrás?', 'ARTINC' => '9201', 'RESP1' => 'La maniobra cause obstrucciones en la circulación.', 'RESP2' => 'La distancia a recorrer sea de 20 metros.', 'RESP3' => 'Se realice en un túnel.', 'CORRECTA' => 2, 'TEMATICA' => 4],
        ['ID' => 20, 'NRO' => 821, 'TEXTO' => 'Si usted circula conduciendo su vehículo por una vía, y va ser adelantado está obligado a:', 'ARTINC' => '8601', 'RESP1' => 'Aumentar la velocidad, permitiéndole el paso. ', 'RESP2' => 'No efectuar maniobras que impidan o dificulten el adelantamiento.', 'RESP3' => 'No permitir el adelantamiento una vez iniciada la maniobra por el otro vehículo.', 'CORRECTA' => 2, 'TEMATICA' => 4],
        // Agregar las 80 preguntas restantes aquí...
    ];

    // Limpiar preguntas existentes
    echo "Limpiando preguntas existentes...\n";
    $pdo->exec("DELETE FROM questions");
    echo "✓ Preguntas eliminadas\n\n";

    // Insertar preguntas
    echo "Insertando preguntas...\n";
    $stmt = $pdo->prepare("INSERT INTO questions (id, category_id, nro, question_text, answer1, answer2, answer3, correct_answer, article_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    foreach ($questions as $question) {
        $params = [
            $question['ID'],
            $question['TEMATICA'],
            $question['NRO'],
            $question['TEXTO'],
            $question['RESP1'],
            $question['RESP2'],
            $question['RESP3'],
            $question['CORRECTA'],
            $question['ARTINC']
        ];
        $stmt->execute($params);
        $count++;
    }
    echo "✓ Se insertaron $count preguntas\n\n";

    // Actualizar contadores de categorías
    echo "Actualizando contadores de categorías...\n";
    $stmt = $pdo->prepare("UPDATE categories SET question_count = (SELECT COUNT(*) FROM questions WHERE category_id = categories.id AND deleted_at IS NULL)");
    $stmt->execute();
    echo "✓ Contadores actualizados\n\n";

    // Crear directorio para imágenes si no existe
    $imgDir = 'assets/img/questions/';
    if (!is_dir($imgDir)) {
        mkdir($imgDir, 0755, true);
        echo "✓ Directorio de imágenes creado: $imgDir\n";
    }

    echo "=== INSERCIÓN COMPLETADA ===\n";
    echo "Las preguntas han sido insertadas correctamente.\n";
    echo "Coloca las imágenes en: $imgDir\n";
    echo "Formato de imagen: i{NRO}.png (ej: i10.png, i825.png)\n\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>