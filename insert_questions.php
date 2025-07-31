<?php
/**
 * Script para insertar las preguntas del sistema de test de tránsito
 * Incluye manejo de imágenes basado en el campo NRO
 */

// Configuración
$config = [
    'db_host' => 'localhost',
    'db_name' => 'test_transito',
    'db_user' => 'root',
    'db_pass' => ''
];

echo "=== INSERTANDO PREGUNTAS ===\n\n";

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexión exitosa\n\n";

    // Datos de las preguntas (del JSON proporcionado)
    $questions = [
        [1, 1, 10, 'La señal que realiza el agente de la autoridad, mediante un toque largo de silbato indica:', 'Que los vehículos pueden continuar la marcha.', 'La aproximación a una interrupción en la vía resultando necesario disminuir la velocidad.', 'Que los vehículos deben detenerse.', 1, '6310'],
        [2, 1, 825, '¿Qué deben hacer los conductores que se acerquen a un agente de circulación que tiene el brazo levantado verticalmente?', 'Reanudar la marcha.', 'Disminuir la velocidad.', 'Detenerse de inmediato.', 3, '6301'],
        [3, 1, 844, 'Si usted ingiere bebida alcohólica, que tipo de vehículo está prohibido conducir.', 'Los de uso personal.', 'Ninguno.', 'Los del sector estatal.', 3, '95'],
        [4, 1, 853, 'Los pasajeros están obligados a:', 'Permanecer sentados en vehículos con barandas inferiores a 120 centímetros.', 'Descender o abordar con el vehículo detenido y hacerlo por cualquier lado de la vía.', 'Llevar parte del cuerpo u objetos fuera de la cabina o cama.', 1, '148'],
        [5, 1, 880, '¿Qué beneficios ofrece la notificación preventiva?', 'Es una medida profiláctica y educativa.', 'Se paga la mitad del importe.', 'Se aplica solo a los conductores novel.', 1, '0'],
        [6, 2, 835, '¿En qué vías es obligatorio que el conductor de La motocicleta lleve puesto el casco de protección?', 'En toda clase de vías.', 'Sólo en vías urbanas.', 'En cualquier vía, salvo en las calles residenciales.', 1, '7201'],
        [7, 2, 845, 'En una intersección, ¿debe detenerse ante una señal vertical de PARE, si un Agente le indica claramente que puede pasar?', 'Sí, por precaución.', 'No, pero pasaré extremando las precauciones y cediendo el paso a los vehículos que se acerquen por mi derecha.', 'No, porque debo atender al mandato emitido por el Agente.', 3, '62'],
        [8, 2, 850, '¿Se prohíbe ingerir bebidas alcohólicas en los vehículos y su transportación en los compartimientos destinados al conductor y a los pasajeros cuando es evidente que se está consumiendo?', 'Si.', 'No.', 'A veces.', 1, '95'],
        [9, 2, 852, 'Los pasajeros están obligados a:', 'Hacer uso de los medios de protección pasivos instalados en el vehículo.', 'Llevar parte del cuerpo u objetos fuera de la cabina o cama.', 'Descender o abordar con el vehículo detenido y hacerlo por cualquier lado de la vía.', 1, '148'],
        [10, 2, 875, 'El Ministerio del Interior bonificará al conductor que efectúa el pago de las multas en las oficinas habilitadas para ese fin dentro de los:', 'Tres días hábiles siguientes a la notificación de la infracción.', 'Diez días naturales siguientes a la notificación de la infracción.', 'Tres días naturales siguientes a la notificación de la infracción.', 1, '0'],
        [11, 3, 81, 'Al aproximarse a un paso a nivel con barreras u otras señales sonoras o lumínicas, el conductor de cualquier vehículo está obligado a:', 'Moderar la velocidad y tomar precauciones.', 'Detener la marcha.', 'Ceder el paso.', 1, '8002'],
        [12, 3, 86, 'Como conductor de cualquier vehículo, al observar peatones que empiecen a cruzar o se encuentren cruzando la calzada por vías con las marcas tipo cebras, usted está obligado a:', 'Disminuir velocidad.', 'Accionar el claxon o aparato similar.', 'Detener el vehículo y ceder el paso.', 3, '8200'],
        [13, 3, 75, 'Usted, en su condición de conductor de cualquier vehículo de motor debe tener en cuenta que las vías que no tengan señalizado su sentido de dirección se consideran de:', 'Doble sentido de circulación.', 'Un solo sentido de circulación.', 'Es un error de señalización.', 1, '7900'],
        [14, 3, 829, '¿Qué debe hacer si al aproximarse a un paso a nivel que no tenga señalización que establezca la conducta a seguir?', 'Detenerme siempre.', 'Detenerme únicamente si observo que no me va a dar tiempo a cruzar el paso sin peligro.', 'Cruzar el paso a nivel si observo que el vehículo sobre raíles ya ha cruzado.', 1, '8001'],
        [15, 3, 851, 'La inmovilización del vehículo finaliza cuando:', 'El conductor sea sustituido por otro habilitado, que ofrece garantías suficientes.', 'No ha desaparecido la causa que lo motivó.', 'Esté habilitado judicial o administrativamente para conducir vehículos.', 1, '97'],
        [16, 4, 811, 'Según lo que observa en la imagen. ¿Está prohibido adelantar detrás del camión pipa que realiza la misma maniobra y no permite la visibilidad de la parte delantera de la vía destinada al sentido contrario?', 'No, porque me sirve de protección.', 'Sí, porque el camión no permite la visibilidad de la parte delantera de la vía destinada al sentido contrario.', 'No, si se circula lentamente.', 2, '8501'],
        [17, 4, 129, '¿Cuándo el conductor de cualquier vehículo puede realizar la maniobra de marcha atrás?', 'A una distancia 30 metros.', 'Obligue a otro vehículo a realizar un cambio en su dirección o velocidad.', 'La velocidad no exceda los 20 kilómetros por hora.', 3, '9202'],
        [18, 4, 34, 'Transitando por una vía de dos carriles y dos sentidos de dirección, usted adelantará a la moto que marcha delante:', 'Por el carril derecho, realizando las señales correspondientes de brazo.', 'Por la derecha, usando el paseo.', 'Por el carril de la izquierda, cumpliendo las exigencias establecidas para el adelantamiento.', 3, '8502'],
        [19, 4, 33, '¿Cuándo se puede realizar una maniobra de marcha atrás?', 'La maniobra cause obstrucciones en la circulación.', 'La distancia a recorrer sea de 20 metros.', 'Se realice en un túnel.', 2, '9201'],
        [20, 4, 821, 'Si usted circula conduciendo su vehículo por una vía, y va ser adelantado está obligado a:', 'Aumentar la velocidad, permitiéndole el paso.', 'No efectuar maniobras que impidan o dificulten el adelantamiento.', 'No permitir el adelantamiento una vez iniciada la maniobra por el otro vehículo.', 2, '8601'],
        [21, 5, 108, '¿Cuándo el conductor de cualquier vehículo puede conducir con menores de 12 años de edad en el asiento delantero?', 'A veces.', 'Nunca.', 'Siempre.', 2, '10207'],
        [22, 5, 274, 'La altura máxima de un vehículo no debe exceder de....', '4 metros.', '3,50 metros.', '5 metros.', 1, '10303'],
        [23, 5, 278, 'La carga útil máxima de un vehículo rígido o articulado no debe sobrepasar de....', '10 toneladas métricas.', '30 toneladas métricas.', '20 toneladas métricas.', 2, '10305'],
        [24, 5, 156, '¿Cuándo puede circular un vehículo con las puertas abiertas?', 'Nunca.', 'A veces.', 'Siempre.', 1, '10310'],
        [25, 5, 166, '¿Dónde se prohíbe adelantar a otro vehículo?', 'A 150 metros de una curva.', 'A 100 metros de un túnel.', 'En un cambio de rasante.', 3, '10402'],
        [26, 6, 766, '¿Puede un conductor de un vehículo en marcha realizar una detención de manera rápida o violenta?', 'Sí, siempre, ya que está permitido totalmente.', 'No, nunca.', 'Sí, a veces, en casos de fuerza mayor y haciendo la señal adecuada.', 3, '10700'],
        [27, 6, 769, '¿Cuándo puede un tractor circular por las vías de interés nacional, provincial o municipal?', 'Siempre, ya que está permitido totalmente.', 'Nunca, ya que está prohibido.', 'Con el permiso del Ministerio del Interior.', 3, '11502'],
        [28, 6, 770, '¿Está permitido que usted conduciendo un automóvil remolque a una bicicleta?', 'Sí, siempre, ya que está permitido totalmente.', 'No, nunca, ya que está prohibido.', 'Si, a veces, con el permiso del Ministerio del Interior.', 2, '11207'],
        [29, 6, 289, '¿Está prohibida la circulación en zonas urbanizadas a los vehículos de trasporte de carga de más de 6 000 kilogramos?', 'No, nunca.', 'Sí, siempre.', 'Sí, salvo con la autorización del Ministerio del Interior.', 3, '11600'],
        [30, 6, 179, '¿Cuándo se permite el trasbordo de mercancía o cualquier objeto de un vehículo a otro en la vía?', 'En caso de accidente, rotura o desperfecto técnico.', 'Cuando se obstruye la circulación solo por media hora.', 'Cuando obstruya la circulación.', 1, '11000'],
        [31, 7, 778, '¿Está prohibido fumar a los pasajeros de los vehículos de motor?', 'Sí, cuando se circula en vehículo de transporte público urbano de pasajeros o de transporte de materiales inflamables o explosivos.', 'Sí, en cualquier caso.', 'No, nunca.', 2, '14808'],
        [32, 7, 194, '¿Qué tipo de vehículo tiene prohibida la circulación por los túneles?', 'Omnibus articulados.', 'Vehículos cuyo peso máximo autorizado no exceda de los 6000 kilogramos.', 'Vehículos que en su circulación no son capaces de alcanzar y mantener una velocidad de 60 kilómetros por hora.', 3, '12302'],
        [33, 7, 195, '¿Cuándo se permite la reparación de cualquier vehículo en la vía?', 'Nunca.', 'A veces, en casos de fuerza mayor y por el tiempo mínimo indispensable.', 'Siempre.', 2, '12500'],
        [34, 7, 197, '¿Está prohibido remolcar a otro con una cuerda o cable no mayor de ocho metros?', 'Siempre.', 'A veces, cuando el sistema de frenos del remolcado está defectuoso.', 'Nunca.', 2, '12501'],
        [35, 7, 199, '¿Cuándo un vehículo remolcado debe estar provisto de bandera roja, u otro medio similar, en la parte trasera o en otro lugar perceptible?', 'Siempre.', 'Nunca.', 'A veces.', 1, '12502'],
        [36, 8, 421, 'El conductor de cualquier vehículo de motor está obligado a moderar la velocidad y detener la marcha, si preciso fuere:', 'Al transitar por una vía preferencial.', 'Cuando un peatón se encuentre en la calzada.', 'Frente a las unidades de la Policía Nacional Revolucionaria.', 2, '12807'],
        [37, 8, 407, 'Usted, como conductor de cualquier vehículo de motor debe tener en cuenta que:', 'En zonas de niños, la velocidad no excederá de 40 kilómetros por hora en zona urbana, los días y horas laborables.', 'Los autos circularan como máximo a 70 kilómetros por hora en las carreteras.', 'No se excederán los 40 kilómetros por hora de velocidad al circular por un camino de tierra o terraplén.', 1, '12705'],
        [38, 8, 385, 'Transitando usted por una carretera, conduciendo un vehículo rígido o articulado, destinado al transporte de carga, la velocidad que podrá desarrollar en su desplazamiento no excederá de:', '80 kilómetros por hora.', '90 kilómetros por hora.', '100 kilómetros por hora.', 1, '12603'],
        [39, 8, 389, 'Conduciendo usted por una autopista, un ómnibus, la velocidad que puede alcanzar con su vehículo no será superior a:', '70 kilómetros por hora.', '100 kilómetros por hora.', '80 kilómetros por hora.', 2, '12605'],
        [40, 8, 397, 'Circulando usted por una carretera, conduciendo una moto, la velocidad para su desplazamiento no puede exceder:', '70 kilómetros por hora.', '90 kilómetros por hora.', '80 kilómetros por hora.', 2, '12602'],
        [41, 9, 347, 'Cuando usted conduzca un vehículo de carga, solo o en caravana, deberá:', 'Llevar encendidas las luces altas o largas, en el horario comprendido entre el atardecer y el anochecer.', 'Que viaje un compañero responsable en la cabina y otro en la cama del camión.', 'Que el vehículo posea techo.', 2, '13601'],
        [42, 9, 290, '¿Podrá un vehículo de motor para el transporte de carga circular sin la hoja de ruta?', 'No, nunca.', 'Sí, a veces.', 'Sí, siempre.', 1, '13001'],
        [43, 9, 294, 'La carga de un vehículo debe estar acondicionada y sujeta de modo que...', 'No haga ruidos.', 'No estorbe la visibilidad del conductor.', 'No se moje.', 2, '13103'],
        [44, 9, 300, '¿Está autorizado a circular un vehículo cargado cuando la altura de la carga exceda de 4 metros sobre el pavimento?', 'Sí, siempre.', 'No.', 'Sí, a veces, con autorización de su empresa.', 2, '13201'],
        [45, 9, 818, 'En una vía secundaria de poco tránsito con ancho suficiente para acomodar tres hileras de vehículos en un único sentido de circulación, ¿podrá estacionar su vehículo en el lado izquierdo de la calzada?', 'Sí.', 'No, porque está prohibido.', 'A veces.', 1, '13702'],
        [46, 10, 198, '¿Está prohibido parquear en la parte de la vía que circundan las islas o rotondas situadas en la confluencia de las vías.?', 'No, nunca.', 'Sí, siempre.', 'Sí, a veces.', 2, '13905'],
        [47, 10, 184, '¿Qué distancia debe de existir entre dos vehículos en estacionamientos?', 'Más de 50 centímetros.', '40 centímetros.', '45 centímetros.', 1, '13004'],
        [48, 10, 180, '¿Dónde se realiza el estacionamiento en calzadas de una dirección?', 'Junto a la acera o borde derecho y en el sentido de la circulación.', 'Junto a la acera o borde izquierdo y en el sentido del tránsito.', 'De forma oblicua a la vía.', 2, '13702'],
        [49, 10, 182, '¿Qué distancia debe de existir entre las ruedas y el contén de la acera cuando se realiza el estacionamiento?', '20 centímetros.', '15 centímetros.', 'Hasta 10 centímetros', 3, '13703'],
        [50, 10, 832, 'En una intersección, ¿podrá parar o estacionar su vehículo si, al hacerlo, dificulta el giro a otros vehículos?', 'No, porque está prohibido.', 'Podré parar, pero no estacionar.', 'Sí, cuando no haya señal que prohíba parar o estacionar y se pueda pasar, aún con dificultades.', 1, '13918'],
        [51, 11, 780, '¿Está prohibido arrojar a la vía algún objeto, sustancia o material?', 'Sí, a veces, cuando pueda causar perjuicios o accidentes.', 'Sí, de cualquier tipo.', 'No, nunca.', 2, '15003'],
        [52, 11, 781, '¿Está permitido que usted conduciendo un automóvil remolque a una bicicleta?', 'Sí, siempre, ya que está permitido totalmente.', 'No, nunca, ya que está prohibido.', 'Si, a veces, con el permiso del Ministerio del Interior.', 2, '15202'],
        [53, 11, 783, 'La luz verde intermitente alerta a los usuarios de la vía que...:', 'Su tiempo acaba de comenzar.', 'Su tiempo está por concluir.', 'Ambas cosas.', 2, '15200'],
        [54, 11, 140, 'Usted, como conductor de cualquier vehículo debe tener en cuenta que la luz roja del semáforo indica que debe:', 'Detenerse.', 'Moderar velocidad y parar si es necesario.', 'Ceder el paso.', 1, '15001'],
        [55, 11, 151, '¿Qué hacen los conductores ante la luz amarilla intermitente del semáforo?', 'Continuar la marcha con precaución.', 'Detenerse en la línea de "Pare".', 'Disminuir la velocidad y parar si es necesario.', 1, '15102'],
        [56, 12, 739, '¿Cuántas de estas señales son de prioridad?', 'Una.', 'Dos.', 'Tres.', 1, '16100'],
        [57, 12, 492, '¿Qué indica esta señal de peligro:', 'Indica el cruce de una carretera o camino con otra de igual categoría.', 'Un entronque lateral derecho.', 'Un entronque lateral izquierdo.', 1, '14804'],
        [58, 12, 493, '¿Se debe ceder el paso a los vehículos que se incorporen a la vía en que está colocada esta señal?', 'A veces.', 'Sí.', 'No.', 3, '16001'],
        [59, 12, 502, '¿Qué significa esta señal?', 'Detener la marcha.', 'Moderar la velocidad.', 'Continuar a la velocidad máxima de la vía.', 2, '16025'],
        [60, 12, 463, 'Esta señal indica...:', 'El límite máximo de velocidad a que se puede circular.', 'La conveniencia de no revasar la velocidad señalada.', 'El límite mínimo de velocidad a que se puede circular.', 2, '17221'],
        [61, 13, 786, 'Si existe sobre el pavimento la línea de "PARE" ¿Qué es lo que indica?', 'Detenerse sobre ella.', 'Detenerse ante ella por estar ante la señal de "Pare" o ante la señal de luz amarilla o roja del semáforo.', 'Que está próxima una intersección sin prioridad.', 2, '17901'],
        [62, 13, 814, 'Este triángulo sobre la calzada indica...:', 'Detención obligatoria.', 'Intersección con prioridad.', 'La obligación que tiene en la próxima intersección de ceder el paso.', 3, '17902'],
        [63, 13, 784, 'En las señales horizontales ¿Qué es lo que indica la regulación?', 'El color de la pintura de la marca.', 'El diseño de su trazado en el pavimento.', 'El lugar donde está ubicada.', 2, '17602'],
        [64, 13, 476, 'La línea continua de canalización significa...:', 'Indican el sentido y la dirección correcta que deben tomar los conductores de vehículos.', 'Indica que la vía es de doble sentido.', 'Delimitan los carriles en áreas críticas de la circulación.', 3, '17806'],
        [65, 13, 470, 'Esta marca significa...:', 'Que ningún vehículo podrá cruzarla o circular sobre ella.', 'Que se está próximo a una intersección.', 'Que puede cruzarse cuando el volumen vehicular así lo requiera.', 1, '17801'],
        [66, 14, 249, '¿Queda prohibida la circulación de un vehículo de motor cuando existen salideros de combustible?', 'Sí.', 'No.', 'A veces, cuando hay mucho tránsito.', 1, '18204'],
        [67, 14, 248, '¿Queda prohibida la circulación de un vehículo de motor cuando se desconectan las velocidades de la caja?', 'A veces.', 'No.', 'Sí.', 3, '18204'],
        [68, 14, 247, '¿Queda prohibida la circulación de un vehículo de motor cuando vibra la trasmisión?', 'Sí, siempre.', 'No, nunca.', 'Sí, a veces, cuando se va despacio.', 1, '18204'],
        [69, 14, 252, '¿Queda prohibida la circulación de un vehículo de motor cuando la superficie de los neumáticos presenta el desgaste máximo?', 'No.', 'Sí.', 'A veces.', 2, '18203'],
        [70, 14, 253, '¿Queda prohibida la circulación de un vehículo de motor cuando existen roturas en las cuerdas de los neumáticos?', 'A veces.', 'No.', 'Sí.', 3, '18203'],
        [71, 15, 328, 'En la zona rural de noche, cuando no se le pide el cambio de luces, ¿a qué distancia aproximada lo debe realizar para no deslumbrar al que viene de frente?', '150 metros.', '100 metros.', '50 metros.', 1, '18604'],
        [72, 15, 319, 'Las luces de cruce o cortas deben alumbrar la vía con eficacia hasta una distancia por delante del vehículo de...', '40 metros.', '50 metros.', '30 metros.', 1, '18401'],
        [73, 15, 320, 'Las luces de carretera o largas deben alumbrar con eficacia la vía una distancia mínima de....', '150 metros.', '90 metros.', '100 metros.', 3, '18402'],
        [74, 15, 323, '¿Es obligatoria la luz que ilumina la chapa trasera al circular de noche?', 'Sólo en los autos ligeros.', 'No, pero es aconsejable.', 'Sí.', 3, '18409'],
        [75, 15, 321, '¿Cuántas luces de posición deben tener encendidas los vehículos que circulen en las horas comprendidas del anochecer al amanecer?', 'Dos delante.', 'Dos delante y dos detrás.', 'Dos detrás.', 2, '18404'],
        [76, 16, 722, '¿Se puede instalar y usar en un vehículo una sirena o aparato similar que produzca ruidos intensos o estridentes?', 'No, nunca, ya que está prohibido totalmente.', 'Sí, siempre, ya que permitido para todos.', 'Sí, a veces, en vehículos con régimen especial.', 3, '19200'],
        [77, 16, 723, '¿Cuántos espejos retrovisores debe tener un vehículo?', 'Sólo dos.', 'Depende del tipo de vehículo.', 'Uno solamente.', 2, '19300'],
        [78, 16, 724, 'Los vehículos que están obligados a estar provistos de un cinturón de seguridad ¿Cuándo deben usarlo?', 'Nunca.', 'Siempre.', 'A veces, solo en zona rural.', 2, '8400'],
        [79, 16, 725, '¿Qué tipos de vehículos están obligados a estar provistos de parabrisas?', 'Todos los vehículos de motor.', 'Todos los vehículos.', 'Todos los automóviles, excepto las motocicletas.', 3, '19501'],
        [80, 16, 726, '¿Está permitido llevar fijado en los parabrisas o cristales, cortinas, anuncios, calcomanías u otros similares que limiten la visibilidad del conductor?', 'Sí, en cualquier caso.', 'Sí, si se tiene espejo retrovisor.', 'No, nunca, por estar prohibido.', 3, '19503'],
        [81, 17, 790, 'Para conducir un ómnibus ¿Qué categoría de licencia se necesita?', 'Categoría "C".', 'Categoría "D".', 'Categoría "E".', 2, '26404'],
        [82, 17, 791, '¿Cuándo un titular de una licencia de conducción puede ser sometido a nuevos exámenes médico, teórico y práctico?', 'Al llegar a 60 años de edad.', 'Al existir motivos para ello, fundados en el expediente del conductor o que obren en otros informes o antecedentes.', 'Al sorprenderse conduciendo en estado de embriaguez.', 2, '28400'],
        [83, 17, 826, 'Desde hace más de dos años, usted es titular de la licencia que autoriza a conducir vehículos de carga, ¿podrá usted optar por la categoría E que autoriza a conducir vehículos articulados?', 'Si.', 'No.', 'Debo esperar tres años.', 1, '265'],
        [84, 17, 827, 'Usted deberá solicitar al órgano de Licencia de Conducción la expedición de un duplicado de su licencia de conducción', 'Cada 5 años, a contar desde la fecha de expedición.', 'Por deterioro, pérdida o robo de la misma.', 'Cuando obtenga cualquier otro permiso de conducción.', 2, '261'],
        [85, 17, 834, 'Con la licencia de conducción categoría A, ¿está permitido conducir ciclomotores?', 'Sí, cualquier ciclomotor.', 'Sí, pero sólo ciclomotores de dos ruedas.', 'No, es imprescindible poseer la categoría A1.', 1, '26401'],
        [86, 18, 793, 'En el acto de imposición de una multa, el conductor deberá firmar la boleta en presencia de la autoridad que la impone ¿Qué significa esta firma?', 'Que reconoce su responsabilidad en la contravención.', 'Una constancia del acto de imposición.', 'Que ya no puedo reclamar la multa.', 2, '30500'],
        [87, 18, 797, '¿Prescriben las infracciones del tránsito al transcurrir un año contado a partir de la notificación?', 'Sí, en todo los casos.', 'No.', 'Sí, generalmente, excepto cuando se haya requerido al infractor para que pague.', 3, '30500'],
        [88, 18, 798, 'Si usted realiza la reclamación de una multa de tránsito dentro de los 10 días hábiles a partir de su notificación, ¿Esa acción le exime del pago del importe de la misma dentro de los plazos previstos?', 'Sí, en todo los caso.', 'No.', 'Sí, a veces.', 2, '30500'],
        [89, 18, 382, '¿Qué sucede si el pago de una multa se efectúa después de transcurrido el plazo de treinta días naturales siguientes a la notificación, sin exceder los sesenta?', 'El importe de la multa se triplica.', 'El importe de la multa se mantiene, pero le ocupan la licencia.', 'El importe de la multa se duplica.', 3, '30500'],
        [90, 18, 377, '¿Podrán disponer la suspensión de la licencia de conducción cuando no haya hecho efectivo el pago de una multa, transcurrido el plazo de sesenta días naturales desde su fecha de imposición?', 'Sí, por seis meses.', 'Sí, por un periodo que puede oscilar entre uno y tres meses.', 'No, sólo se le exigirá el pago.', 2, '28801'],
        [91, 19, 704, 'El que conduzca un vehículo, que infringiendo las leyes o reglamentos del tránsito, cause la muerte a una persona incurre en:', 'Sanción de privación de libertad.', 'Cancelación de la licencia de conducción.', 'Suspensión de la licencia de conducción.', 1, '177'],
        [92, 19, 705, 'El que conduzca un vehículo, que infringiendo las leyes o reglamentos del tránsito, cause lesiones graves o dañe gravemente la salud a una persona incurre en:', 'Multa de 1500.00 pesos y resarsir los daños causados.', 'Cancelación de la licencia de conducción.', 'Sanción de privación de libertad.', 3, '178'],
        [93, 19, 707, 'El que conduzca un vehículo, que infringiendo las leyes o reglamentos del tránsito, cause daños de valor considerable a bienes de ajena pertenencia incurre en:', 'Multa de 300.00 pesos.', 'Cancelación de la licencia de conducción.', 'Suspensión de la licencia de conducción.', 1, '17901'],
        [94, 19, 710, 'El que, conduzca un vehículo por la vía pública, encontrándose en estado de embriaguez alcohólica, podrá ser sancionado a:', 'Cancelación de la licencia de conducción.', 'Suspensión de la licencia de conducción.', 'Multa de 200.00 pesos.', 3, '0'],
        [95, 19, 712, 'El que, conduzca un vehículo por la vía pública, habiendo ingerido bebidas alcohólicas en cantidad suficiente para afectar su capacidad de conducción, aunque sin llegar a estado de embriaguez incurre en:', 'Multa de 100.00 pesos.', 'Ocuparle la chapa del vehículo.', 'Suspensión de la licencia de conducción.', 1, '18102'],
        [96, 20, 803, 'Un conductor de un vehículo de carga articulado circula de noche por una carretera a 90 kilómetros por hora, al darse cruce con otro vehículo que circula en sentido contrario, realiza el cambio de luz cuando está a una distancia de 50 metros del mismo. ¿Cuántas infracciones cometió?', '2 Infracciones.', '1 infracción.', '3 infracciones.', 1, '0'],
        [97, 20, 720, 'El conductor de un vehículo al estacionarlo en una vía con pendiente ascendente (hacia arriba), apaga el motor del vehículo, cierra el chucho y retira la llave, además coloca el freno de seguridad o emergencia, conecta la marcha atrás y gira el volante del timón en dirección al contén. ¿Cuántas infracciones cometió este conductor?', '2 infracciones.', '3 infracciones.', '4 infracciones.', 1, '0'],
        [98, 20, 718, 'El conductor de una motocicleta, transita por la zona urbana a 60 kilómetros por hora, sin el casco protector y lleva sólo en el sidecar a un niño de 7 años, el cual va sonriente. ¿Cuántas infracciones se comprueban?', '4 infracciones.', '2 infracciones.', '3 infracciones.', 3, '0'],
        [99, 20, 694, 'El conductor de una moto sin sidecar realiza maniobras de acrobacia durante su marcha en una vía urbana de poco tránsito, luego recoge a su hijo de 6 años para llevarlo a la escuela, al llegar a una intersección regulada por semáforo y al casi finalizar el tiempo de verde cruza la misma a 50 kilómetros por hora. ¿Cuántas infracciones ha cometido?', '4 infracciones.', '2 infracciones.', '3 infracciones.', 2, '0'],
        [100, 20, 696, 'Por una carretera se desplaza un vehículo de carga pendiente abajo, con la trasmisión en neutro para ahorrar combustible, continúa su recorrido y como es hora de almuerzo su chofer lo detiene y estaciona sobre la acera, durante el almuerzo solo ingirió una cerveza e inmediatamente después continuó su marcha. ¿Cuántas infracciones cometió?', '3 infracciones.', '2 infracciones.', '4 infracciones.', 1, '0']
    ];

    // Eliminar preguntas existentes
    echo "Eliminando preguntas existentes...\n";
    $pdo->exec("DELETE FROM questions");
    $pdo->exec("ALTER TABLE questions AUTO_INCREMENT = 1");
    
    // Insertar preguntas
    echo "Insertando preguntas...\n";
    $stmt = $pdo->prepare("INSERT INTO questions (id, category_id, nro, question_text, answer1, answer2, answer3, correct_answer, article_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $count = 0;
    foreach ($questions as $question) {
        $stmt->execute($question);
        $count++;
        if ($count % 10 == 0) {
            echo "Insertadas $count preguntas...\n";
        }
    }
    
    echo "✓ Se insertaron $count preguntas\n\n";
    
    // Actualizar contadores de categorías
    echo "Actualizando contadores de categorías...\n";
    $pdo->exec("UPDATE categories SET question_count = (
        SELECT COUNT(*) FROM questions WHERE category_id = categories.id AND deleted_at IS NULL
    )");
    
    echo "✓ Contadores actualizados\n\n";
    
    // Crear directorio para imágenes si no existe
    $imgDir = 'assets/img/questions/';
    if (!is_dir($imgDir)) {
        mkdir($imgDir, 0755, true);
        echo "✓ Directorio de imágenes creado: $imgDir\n";
    }
    
    echo "=== PREGUNTAS INSERTADAS EXITOSAMENTE ===\n";
    echo "Se insertaron $count preguntas en 20 categorías.\n";
    echo "Para las imágenes:\n";
    echo "- Coloca las imágenes en: $imgDir\n";
    echo "- Nombra las imágenes como: i{NRO}.png (ej: i10.png, i825.png)\n";
    echo "- El sistema buscará automáticamente las imágenes basándose en el campo NRO\n\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>