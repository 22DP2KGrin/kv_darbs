const SPANISH_EXERCISE_DATA = {
    'spanish-past-tense': {
        title: 'Pretérito indefinido',
        subtitle: 'Trenē vienkāršo pagātni spāņu valodā',
        exerciseType: 'grammar',
        questions: [
            { question: '1. Ayer yo _____ al centro.', options: ['fui', 'voy', 'iba', 'iré'], correctAnswer: 0 },
            { question: '2. Nosotros _____ una paella el sábado.', options: ['comemos', 'comimos', 'comeremos', 'comían'], correctAnswer: 1 },
            { question: '3. Ella _____ en Madrid el año pasado.', options: ['vive', 'vivió', 'vivía', 'vivirá'], correctAnswer: 1 },
            { question: '4. ¿Qué _____ tú ayer por la tarde?', options: ['haces', 'hiciste', 'hacías', 'harás'], correctAnswer: 1 },
            { question: '5. Mis amigos _____ tarde a la fiesta.', options: ['llegaron', 'llegan', 'llegaban', 'llegarán'], correctAnswer: 0 },
            { question: '6. El mes pasado nosotros _____ a Barcelona.', options: ['viajamos', 'viajábamos', 'viajaremos', 'viajáis'], correctAnswer: 0 },
            { question: '7. Yo no _____ café esta mañana.', options: ['bebí', 'bebo', 'bebía', 'beberé'], correctAnswer: 0 },
            { question: '8. Anoche ellos _____ una película.', options: ['ven', 'veían', 'vieron', 'verán'], correctAnswer: 2 },
            { question: '9. El profesor _____ la clase a tiempo.', options: ['empezó', 'empieza', 'empezaba', 'empezará'], correctAnswer: 0 },
            { question: '10. ¿Dónde _____ vosotros las vacaciones?', options: ['pasasteis', 'pasáis', 'pasabais', 'pasaréis'], correctAnswer: 0 },
            { question: '11. Mi hermana _____ el informe ayer.', options: ['escribe', 'escribió', 'escribía', 'escribirá'], correctAnswer: 1 },
            { question: '12. Nosotros _____ muy cansados después del viaje.', options: ['estuvimos', 'estamos', 'estábamos', 'estaremos'], correctAnswer: 0 }
        ]
    },
    'spanish-travel-vocabulary': {
        title: 'Viajes y direcciones',
        subtitle: 'Vārdu krājums par ceļošanu, transportu un ceļa jautāšanu',
        exerciseType: 'vocabulary',
        questions: [
            { question: '1. Ko nozīmē “billete”?', options: ['Koferis', 'Biļete', 'Pase', 'Viesnīca'], correctAnswer: 1 },
            { question: '2. Kurš vārds nozīmē “lidosta”?', options: ['aeropuerto', 'estación', 'carretera', 'mapa'], correctAnswer: 0 },
            { question: '3. Kā spāniski pateikt “Kur ir metro stacija?”', options: ['¿Dónde está la estación de metro?', '¿Cuándo sale el tren?', '¿Qué hora es?', '¿Cuánto cuesta?'], correctAnswer: 0 },
            { question: '4. Ko nozīmē “girar a la derecha”?', options: ['Iet taisni', 'Pagriezties pa kreisi', 'Pagriezties pa labi', 'Apstāties'], correctAnswer: 2 },
            { question: '5. Kurš vārds nozīmē “čemodāns”?', options: ['maleta', 'ventana', 'cartera', 'habitación'], correctAnswer: 0 },
            { question: '6. “Ida y vuelta” nozīmē:', options: ['Vienvirziena biļete', 'Turp un atpakaļ', 'Nokavēts lidojums', 'Brauciens ar autobusu'], correctAnswer: 1 },
            { question: '7. Kā spāniski ir “karte” ceļojuma nozīmē?', options: ['mapa', 'mesa', 'mano', 'mochila'], correctAnswer: 0 },
            { question: '8. Ko nozīmē “alojamiento”?', options: ['Ceļš', 'Naktsmītne', 'Bagāža', 'Pase'], correctAnswer: 1 },
            { question: '9. Kā pateikt “Es gribu rezervēt istabu”?', options: ['Quiero comprar una maleta.', 'Quiero reservar una habitación.', 'Quiero tomar un taxi.', 'Quiero visitar un museo.'], correctAnswer: 1 },
            { question: '10. “Retraso” visbiežāk nozīmē:', options: ['Atlaide', 'Aizkavēšanās', 'Pārbaude', 'Pagrieziens'], correctAnswer: 1 },
            { question: '11. Ko nozīmē “seguir todo recto”?', options: ['Pagriezties pa labi', 'Iet atpakaļ', 'Iet visu laiku taisni', 'Nopirkt biļeti'], correctAnswer: 2 },
            { question: '12. Kurš vārds nozīmē “robeža”?', options: ['frontera', 'oficina', 'pasillo', 'ventanilla'], correctAnswer: 0 }
        ]
    },
    'spanish-restaurant-dialogues': {
        title: 'En el restaurante',
        subtitle: 'Lasīšana un frāzes par ēdienu pasūtīšanu restorānā',
        exerciseType: 'reading',
        questions: [
            { question: '1. Ko nozīmē “la cuenta, por favor”?', options: ['Ēdienkarti, lūdzu', 'Rēķinu, lūdzu', 'Ūdeni, lūdzu', 'Galdu, lūdzu'], correctAnswer: 1 },
            { question: '2. “¿Qué me recomienda?” restorānā nozīmē:', options: ['Kur ir tualete?', 'Ko jūs iesakāt?', 'Vai tas ir dārgi?', 'Kad jūs aizverat?'], correctAnswer: 1 },
            { question: '3. Kurš vārds nozīmē “uzkoda”?', options: ['postre', 'entrada', 'bebida', 'cuchara'], correctAnswer: 1 },
            { question: '4. “Quisiera una mesa para dos” nozīmē:', options: ['Es gribu pasūtīt desertu', 'Es vēlētos galdu diviem', 'Es meklēju viesnīcu', 'Es samaksāju skaidrā naudā'], correctAnswer: 1 },
            { question: '5. Ko nozīmē “postre”?', options: ['Dzēriens', 'Pamatēdiens', 'Deserts', 'Rēķins'], correctAnswer: 2 },
            { question: '6. “Sin cebolla” nozīmē:', options: ['Ar sīpoliem', 'Bez sīpoliem', 'Ar tomātiem', 'Bez sāls'], correctAnswer: 1 },
            { question: '7. Ko nozīmē “camarero”?', options: ['Pavārs', 'Klients', 'Viesmīlis', 'Vadītājs'], correctAnswer: 2 },
            { question: '8. “Está muy rico” visdrīzāk nozīmē:', options: ['Tas ir ļoti dārgs', 'Tas ir ļoti garšīgs', 'Tas ir ļoti karsts', 'Tas ir ļoti mazs'], correctAnswer: 1 },
            { question: '9. Kurš vārds nozīmē “dakša”?', options: ['cuchillo', 'tenedor', 'plato', 'vaso'], correctAnswer: 1 },
            { question: '10. “¿Puedo pagar con tarjeta?” nozīmē:', options: ['Vai varu maksāt ar karti?', 'Vai varu paņemt līdz?', 'Vai ir brīvs galds?', 'Vai tas ir pikants?'], correctAnswer: 0 },
            { question: '11. Ko nozīmē “bebida”?', options: ['Dzēriens', 'Uzkoda', 'Salvete', 'Tējkarote'], correctAnswer: 0 },
            { question: '12. “Para llevar” nozīmē:', options: ['Ēst uz vietas', 'Līdzi ņemšanai', 'Tikai dzērieniem', 'Rezervēts'], correctAnswer: 1 }
        ]
    },
    'spanish-opinion-connectors': {
        title: 'Expresar opiniones',
        subtitle: 'Frāzes, ar kurām izteikt viedokli un saistīt idejas',
        exerciseType: 'writing',
        questions: [
            { question: '1. Kurš variants nozīmē “manuprāt”?', options: ['por supuesto', 'en mi opinión', 'sin embargo', 'por ejemplo'], correctAnswer: 1 },
            { question: '2. “Sin embargo” nozīmē:', options: ['tāpēc', 'tomēr', 'piemēram', 'pirmkārt'], correctAnswer: 1 },
            { question: '3. Ko lieto, ja gribi pievienot piemēru?', options: ['por ejemplo', 'además', 'aunque', 'quizás'], correctAnswer: 0 },
            { question: '4. “Estoy de acuerdo” nozīmē:', options: ['Es nepiekrītu', 'Es piekrītu', 'Es šaubos', 'Es aizmirsu'], correctAnswer: 1 },
            { question: '5. Kurš savienotājs nozīmē “turklāt”?', options: ['además', 'pero', 'porque', 'si'], correctAnswer: 0 },
            { question: '6. “No estoy seguro, pero…” vislabāk lieto, kad:', options: ['pilnīgi piekrīti', 'esi piesardzīgs ar viedokli', 'dod pavēli', 'stāsti par pagātni'], correctAnswer: 1 },
            { question: '7. “Porque” nozīmē:', options: ['kad', 'jo', 'ja', 'lai gan'], correctAnswer: 1 },
            { question: '8. Ko nozīmē “aunque”?', options: ['kaut gan', 'jo', 'arī', 'vispirms'], correctAnswer: 0 },
            { question: '9. “Desde mi punto de vista” nozīmē:', options: ['No mana skatpunkta', 'No vakardienas', 'No sākuma', 'No kreisās puses'], correctAnswer: 0 },
            { question: '10. Kurš variants nozīmē “noslēgumā”?', options: ['por fin', 'en conclusión', 'además', 'quizás'], correctAnswer: 1 },
            { question: '11. “Creo que…” nozīmē:', options: ['Es redzu, ka', 'Es domāju, ka', 'Es zinu, ka', 'Es dzirdu, ka'], correctAnswer: 1 },
            { question: '12. Kurš savienotājs palīdz pretstatīt idejas?', options: ['pero', 'y', 'también', 'entonces'], correctAnswer: 0 }
        ]
    },
    'spanish-subjunctive-basics': {
        title: 'Subjuntivo básico',
        subtitle: 'Pamata ievads spāņu subjunktīvā',
        exerciseType: 'grammar',
        questions: [
            { question: '1. Quiero que tú _____ más despacio.', options: ['hablas', 'hables', 'hablar', 'hablabas'], correctAnswer: 1 },
            { question: '2. Es importante que nosotros _____ a tiempo.', options: ['llegamos', 'lleguemos', 'llegar', 'llegábamos'], correctAnswer: 1 },
            { question: '3. Espero que ella _____ bien el examen.', options: ['hace', 'hará', 'haga', 'hacía'], correctAnswer: 2 },
            { question: '4. Dudo que ellos _____ hoy.', options: ['vienen', 'vendrán', 'vengan', 'venían'], correctAnswer: 2 },
            { question: '5. Es bueno que tú me _____.', options: ['escuches', 'escuchas', 'escucharás', 'escuchaste'], correctAnswer: 0 },
            { question: '6. No creo que él _____ razón.', options: ['tiene', 'tenga', 'tuvo', 'tendrá'], correctAnswer: 1 },
            { question: '7. Ojalá _____ sol mañana.', options: ['hace', 'hará', 'haga', 'hacemos'], correctAnswer: 2 },
            { question: '8. Busco a alguien que _____ español.', options: ['habla', 'hable', 'habló', 'hablará'], correctAnswer: 1 },
            { question: '9. Me alegra que vosotros _____ aquí.', options: ['estáis', 'estéis', 'estabais', 'estaréis'], correctAnswer: 1 },
            { question: '10. Es posible que nosotros _____ tarde.', options: ['salimos', 'salgamos', 'saldrán', 'salíamos'], correctAnswer: 1 },
            { question: '11. Quieren que yo _____ más.', options: ['estudio', 'estudie', 'estudié', 'estudiaré'], correctAnswer: 1 },
            { question: '12. Es raro que Ana no _____.', options: ['viene', 'vino', 'venga', 'vendrá'], correctAnswer: 2 }
        ]
    },
    'spanish-idiomatic-expressions-advanced': {
        title: 'Expresiones idiomáticas',
        subtitle: 'Mūsdienīgas un bieži lietotas spāņu idiomātiskās frāzes',
        exerciseType: 'vocabulary',
        questions: [
            { question: '1. “Estar en las nubes” nozīmē:', options: ['Būt ļoti aizņemtam', 'Būt apjukušam vai sapņainam', 'Būt lidmašīnā', 'Būt laimīgam'], correctAnswer: 1 },
            { question: '2. “Costar un ojo de la cara” nozīmē:', options: ['Būt ļoti lētam', 'Būt ļoti dārgam', 'Būt bīstamam', 'Būt neērti'], correctAnswer: 1 },
            { question: '3. “Meter la pata” visbiežāk nozīmē:', options: ['Iekāpt peļķē', 'Kļūdīties neveikli', 'Ātri bēgt', 'Stingri nostāties'], correctAnswer: 1 },
            { question: '4. “No tener pelos en la lengua” nozīmē:', options: ['Runāt ļoti atklāti', 'Runāt ļoti klusi', 'Būt saslimušam', 'Būt nogurušam'], correctAnswer: 0 },
            { question: '5. “Ser pan comido” nozīmē:', options: ['Būt garšīgam', 'Būt ļoti vieglam', 'Būt ikdienišķam', 'Būt garlaicīgam'], correctAnswer: 1 },
            { question: '6. “Tomar el pelo” nozīmē:', options: ['Apgriezt matus', 'Jokot vai apmuļķot', 'Dusmoties', 'Steigties'], correctAnswer: 1 },
            { question: '7. “Estar hecho polvo” nozīmē:', options: ['Būt ļoti netīram', 'Būt ļoti nogurušam', 'Būt dusmīgam', 'Būt apģērbtam pelēkā'], correctAnswer: 1 },
            { question: '8. “Ir al grano” nozīmē:', options: ['Runāt tieši par būtisko', 'Iet uz veikalu', 'Pazaudēt domu', 'Strīdēties'], correctAnswer: 0 },
            { question: '9. “Tirar la casa por la ventana” nozīmē:', options: ['Pārvākties', 'Tērēt ļoti daudz naudas', 'Uzņemt viesus', 'Salabot māju'], correctAnswer: 1 },
            { question: '10. “Dar en el clavo” nozīmē:', options: ['Kļūdīties', 'Trāpīt tieši mērķī', 'Strādāt smagi', 'Salabot sienu'], correctAnswer: 1 },
            { question: '11. “Ponerse las pilas” nozīmē:', options: ['Uzlādēt telefonu', 'Saņemties un sākt darīt', 'Pirkt baterijas', 'Aizmigt'], correctAnswer: 1 },
            { question: '12. “Quedarse de piedra” nozīmē:', options: ['Palikt ļoti pārsteigtam', 'Sasalt aukstumā', 'Aizmirst tekstu', 'Nokrist zemē'], correctAnswer: 0 }
        ]
    },
    'spanish-formal-email': {
        title: 'Correo formal',
        subtitle: 'Formāls stils, pieklājīgas frāzes un e-pasta struktūra',
        exerciseType: 'writing',
        questions: [
            { question: '1. Kurš sākums ir vispiemērotākais formālam e-pastam?', options: ['Hola, amigo', 'Buenas', 'Estimado señor / Estimada señora', '¿Qué tal?'], correctAnswer: 2 },
            { question: '2. “Le escribo para…” nozīmē:', options: ['Es jums rakstu, lai…', 'Es gaidu jūsu vēstuli', 'Es nezinu, ko rakstīt', 'Es runāju pa telefonu'], correctAnswer: 0 },
            { question: '3. Kurš noslēgums ir formāls?', options: ['Un abrazo', 'Saludos cordiales', 'Nos vemos', 'Chao'], correctAnswer: 1 },
            { question: '4. “Solicitar información” nozīmē:', options: ['Nosūtīt dāvanu', 'Lūgt informāciju', 'Atcelt tikšanos', 'Mainīt tēmu'], correctAnswer: 1 },
            { question: '5. Kurš variants ir vispieklājīgākais?', options: ['Quiero esto ahora.', 'Mándemelo hoy.', 'Le agradecería su respuesta.', 'Dime rápido.'], correctAnswer: 2 },
            { question: '6. “Adjunto” e-pastā parasti nozīmē:', options: ['Es atbildu', 'Pielikumā pievienoju', 'Es atvainojos', 'Es pārtraukšu sarunu'], correctAnswer: 1 },
            { question: '7. Kurš vietniekvārds ir formālāks par “tú”?', options: ['vosotros', 'usted', 'nosotros', 'ellos'], correctAnswer: 1 },
            { question: '8. “Quedo a la espera de su respuesta” nozīmē:', options: ['Esmu beidzis rakstīt', 'Gaidu jūsu atbildi', 'Es jums piezvanīšu', 'Atbildiet vēlāk'], correctAnswer: 1 },
            { question: '9. Kurš temats ir piemērots formālam e-pastam?', options: ['Holaaa', 'Pregunta', 'Solicitud de información sobre el curso', 'Ey'], correctAnswer: 2 },
            { question: '10. “Atentamente” visbiežāk lieto:', options: ['E-pasta sākumā', 'Starp rindkopām', 'Noslēgumā', 'Faila nosaukumā'], correctAnswer: 2 },
            { question: '11. Kurš variants ir pārāk neformāls?', options: ['Le escribo para confirmar…', 'Muchas gracias por su tiempo.', 'Te mando esto ya.', 'Quedo a su disposición.'], correctAnswer: 2 },
            { question: '12. Formālā vēstulē vēlams:', options: ['Lietot emocijzīmes', 'Rakstīt bez sveiciena', 'Rakstīt skaidri un strukturēti', 'Izmantot tikai saīsinājumus'], correctAnswer: 2 }
        ]
    },
    'spanish-news-and-debate': {
        title: 'Noticias y debate',
        subtitle: 'Advanced reading, arguments and public discussion vocabulary',
        exerciseType: 'reading',
        questions: [
            { question: '1. “Según el artículo” nozīmē:', options: ['Pēc autora domām', 'Saskaņā ar rakstu', 'Raksta beigās', 'Pretēji rakstam'], correctAnswer: 1 },
            { question: '2. Kurš vārds nozīmē “pierādījumi”?', options: ['pruebas', 'dudas', 'quejas', 'firmas'], correctAnswer: 0 },
            { question: '3. “La encuesta muestra que…” nozīmē:', options: ['Intervija noliedz, ka…', 'Aptauja rāda, ka…', 'Likums pieprasa, ka…', 'Žurnālists jautā, vai…'], correctAnswer: 1 },
            { question: '4. Kurš savienotājs vislabāk ievada pretargumentu?', options: ['sin embargo', 'además', 'por eso', 'por ejemplo'], correctAnswer: 0 },
            { question: '5. “La mayoría de los ciudadanos” nozīmē:', options: ['Daži pilsoņi', 'Lielākā daļa pilsoņu', 'Ārvalstu viesi', 'Visi studenti'], correctAnswer: 1 },
            { question: '6. “Tomar medidas” visbiežāk nozīmē:', options: ['Veikt pasākumus / rīkoties', 'Mērīt galdu', 'Mainīt viedokli', 'Rakstīt atskaiti'], correctAnswer: 0 },
            { question: '7. “El gobierno anunció…” nozīmē:', options: ['Valdība paziņoja…', 'Tauta protestēja…', 'Avīze izdzēsa…', 'Pilsēta aizvērās…'], correctAnswer: 0 },
            { question: '8. “A corto plazo” nozīmē:', options: ['Īstermiņā', 'Ilgtermiņā', 'Bez termiņa', 'Pagājušajā gadā'], correctAnswer: 0 },
            { question: '9. Kurš vārds nozīmē “diskusija / debates”?', options: ['debate', 'desayuno', 'descuento', 'derrota'], correctAnswer: 0 },
            { question: '10. “Se plantea la cuestión de…” nozīmē:', options: ['Jautājums par … tiek izvirzīts', 'Jautājums ir atrisināts', 'Jautājums nav svarīgs', 'Jautājums ir aizliegts'], correctAnswer: 0 },
            { question: '11. “Estar a favor de” nozīmē:', options: ['Būt pret', 'Būt par / atbalstīt', 'Būt neitrālam', 'Būt apmulsušam'], correctAnswer: 1 },
            { question: '12. Labs noslēguma teikums debatēs bieži:', options: ['Atkārto galveno secinājumu', 'Ievieš pilnīgi jaunu tēmu', 'Pārtrauc sarunu bez secinājuma', 'Uzdod tikai vienu jautājumu'], correctAnswer: 0 }
        ]
    }
};
