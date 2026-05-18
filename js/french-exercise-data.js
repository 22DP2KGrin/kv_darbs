const FRENCH_EXERCISE_DATA = {
    'french-passe-compose': {
        title: 'Passé composé',
        subtitle: 'Trenē franču pagātni ar avoir un être',
        exerciseType: 'grammar',
        questions: [
            { question: '1. Hier, nous _____ au cinéma.', options: ['avons allé', 'sommes allés', 'allons', 'irons'], correctAnswer: 1 },
            { question: '2. Elle _____ un livre intéressant.', options: ['a lu', 'est lu', 'lit', 'lira'], correctAnswer: 0 },
            { question: '3. Tu _____ tes devoirs hier soir.', options: ['as fini', 'es fini', 'finis', 'finiras'], correctAnswer: 0 },
            { question: '4. Ils _____ très tard après la fête.', options: ['sont rentrés', 'ont rentrés', 'rentrent', 'rentraient'], correctAnswer: 0 },
            { question: '5. J’_____ mes clés ce matin.', options: ['suis perdu', 'ai perdu', 'perds', 'perdrai'], correctAnswer: 1 },
            { question: '6. Marie _____ en train à Paris.', options: ['est arrivée', 'a arrivée', 'arrive', 'arrivait'], correctAnswer: 0 },
            { question: '7. Vous _____ la porte avant de partir.', options: ['avez fermé', 'êtes fermés', 'fermez', 'fermerez'], correctAnswer: 0 },
            { question: '8. On _____ beaucoup pendant les vacances.', options: ['a marché', 'est marché', 'marche', 'marchera'], correctAnswer: 0 },
            { question: '9. Mes amis _____ à huit heures.', options: ['ont parti', 'sont partis', 'partent', 'partaient'], correctAnswer: 1 },
            { question: '10. J’_____ ce film deux fois.', options: ['suis vu', 'ai vu', 'vois', 'verrai'], correctAnswer: 1 },
            { question: '11. Tu _____ dans cette ville en 2024.', options: ['as habité', 'es habité', 'habites', 'habiteras'], correctAnswer: 0 },
            { question: '12. Nous _____ une excellente journée.', options: ['avons passé', 'sommes passés', 'passons', 'passerons'], correctAnswer: 0 }
        ]
    },
    'french-travel-vocabulary': {
        title: 'Voyages et directions',
        subtitle: 'Vārdu krājums par ceļošanu, transportu un ceļa jautāšanu',
        exerciseType: 'vocabulary',
        questions: [
            { question: '1. Ko nozīmē “le billet” ?', options: ['Čemodāns', 'Biļete', 'Stacija', 'Pase'], correctAnswer: 1 },
            { question: '2. Kurš vārds nozīmē “lidosta” ?', options: ['la gare', 'l’aéroport', 'la rue', 'la carte'], correctAnswer: 1 },
            { question: '3. Kā franču valodā pateikt “Kur ir metro stacija?”', options: ['Où est la station de métro ?', 'Quand part le train ?', 'Combien ça coûte ?', 'Quelle heure est-il ?'], correctAnswer: 0 },
            { question: '4. Ko nozīmē “tournez à droite” ?', options: ['Pagriezieties pa kreisi', 'Ejiet taisni', 'Pagriezieties pa labi', 'Apstājieties'], correctAnswer: 2 },
            { question: '5. Kurš vārds nozīmē “viesnīca” ?', options: ['l’hôtel', 'la valise', 'la fenêtre', 'le passeport'], correctAnswer: 0 },
            { question: '6. “Aller-retour” nozīmē:', options: ['Vienvirziena biļete', 'Turp un atpakaļ', 'Aizkavēts reiss', 'Pilsētas karte'], correctAnswer: 1 },
            { question: '7. Kā franču valodā ir “karte” ceļojuma nozīmē?', options: ['la carte', 'la table', 'la route', 'la salle'], correctAnswer: 0 },
            { question: '8. Ko nozīmē “l’hébergement” ?', options: ['Bagāža', 'Naktsmītne', 'Robeža', 'Autobuss'], correctAnswer: 1 },
            { question: '9. Kā pateikt “Es gribu rezervēt istabu” ?', options: ['Je veux acheter une valise.', 'Je voudrais réserver une chambre.', 'Je cherche un taxi.', 'Je vais au musée.'], correctAnswer: 1 },
            { question: '10. “Le retard” visbiežāk nozīmē:', options: ['Atlaide', 'Aizkavēšanās', 'Pagrieziens', 'Pārbaude'], correctAnswer: 1 },
            { question: '11. Ko nozīmē “continuez tout droit” ?', options: ['Pagriezieties pa labi', 'Ejiet visu laiku taisni', 'Atgriezieties atpakaļ', 'Nopērciet biļeti'], correctAnswer: 1 },
            { question: '12. Kurš vārds nozīmē “robeža” ?', options: ['la frontière', 'le quai', 'le bureau', 'la porte'], correctAnswer: 0 }
        ]
    },
    'french-restaurant-dialogues': {
        title: 'Au restaurant',
        subtitle: 'Lasīšana un frāzes ēdiena pasūtīšanai restorānā',
        exerciseType: 'reading',
        questions: [
            { question: '1. Ko nozīmē “L’addition, s’il vous plaît” ?', options: ['Ēdienkarti, lūdzu', 'Rēķinu, lūdzu', 'Galdu, lūdzu', 'Ūdeni, lūdzu'], correctAnswer: 1 },
            { question: '2. “Qu’est-ce que vous me conseillez ?” nozīmē:', options: ['Kur ir tualete?', 'Ko jūs man iesakāt?', 'Vai tas ir dārgi?', 'Kad jūs aizverat?'], correctAnswer: 1 },
            { question: '3. Kurš vārds nozīmē “uzkoda” ?', options: ['le dessert', 'l’entrée', 'la boisson', 'la cuillère'], correctAnswer: 1 },
            { question: '4. “Je voudrais une table pour deux” nozīmē:', options: ['Es gribu pasūtīt desertu', 'Es vēlētos galdu diviem', 'Es meklēju viesnīcu', 'Es samaksāju skaidrā naudā'], correctAnswer: 1 },
            { question: '5. Ko nozīmē “le dessert” ?', options: ['Dzēriens', 'Pamatēdiens', 'Deserts', 'Rēķins'], correctAnswer: 2 },
            { question: '6. “Sans oignon” nozīmē:', options: ['Ar sīpoliem', 'Bez sīpoliem', 'Ar tomātiem', 'Bez sāls'], correctAnswer: 1 },
            { question: '7. Ko nozīmē “le serveur” ?', options: ['Pavārs', 'Klients', 'Viesmīlis', 'Vadītājs'], correctAnswer: 2 },
            { question: '8. “C’est délicieux” visdrīzāk nozīmē:', options: ['Tas ir ļoti dārgs', 'Tas ir ļoti garšīgs', 'Tas ir ļoti karsts', 'Tas ir ļoti mazs'], correctAnswer: 1 },
            { question: '9. Kurš vārds nozīmē “dakša” ?', options: ['le couteau', 'la fourchette', 'l’assiette', 'le verre'], correctAnswer: 1 },
            { question: '10. “Puis-je payer par carte ?” nozīmē:', options: ['Vai varu maksāt ar karti?', 'Vai varu paņemt līdzi?', 'Vai ir brīvs galds?', 'Vai tas ir pikants?'], correctAnswer: 0 },
            { question: '11. Ko nozīmē “la boisson” ?', options: ['Dzēriens', 'Uzkoda', 'Salvete', 'Tējkarote'], correctAnswer: 0 },
            { question: '12. “À emporter” nozīmē:', options: ['Ēst uz vietas', 'Līdzi ņemšanai', 'Tikai dzērieniem', 'Rezervēts'], correctAnswer: 1 }
        ]
    },
    'french-opinion-connectors': {
        title: 'Exprimer son opinion',
        subtitle: 'Frāzes, ar kurām izteikt viedokli un saistīt idejas',
        exerciseType: 'writing',
        questions: [
            { question: '1. Kurš variants nozīmē “manuprāt” ?', options: ['bien sûr', 'à mon avis', 'pourtant', 'par exemple'], correctAnswer: 1 },
            { question: '2. “Cependant” nozīmē:', options: ['tāpēc', 'tomēr', 'piemēram', 'pirmkārt'], correctAnswer: 1 },
            { question: '3. Ko lieto, ja gribi pievienot piemēru?', options: ['par exemple', 'de plus', 'quoique', 'peut-être'], correctAnswer: 0 },
            { question: '4. “Je suis d’accord” nozīmē:', options: ['Es nepiekrītu', 'Es piekrītu', 'Es šaubos', 'Es aizmirsu'], correctAnswer: 1 },
            { question: '5. Kurš savienotājs nozīmē “turklāt” ?', options: ['de plus', 'mais', 'parce que', 'si'], correctAnswer: 0 },
            { question: '6. “Je ne suis pas certain, mais…” vislabāk lieto, kad:', options: ['pilnīgi piekrīti', 'esi piesardzīgs ar viedokli', 'dod pavēli', 'stāsti par pagātni'], correctAnswer: 1 },
            { question: '7. “Parce que” nozīmē:', options: ['kad', 'jo', 'ja', 'lai gan'], correctAnswer: 1 },
            { question: '8. Ko nozīmē “bien que” ?', options: ['kaut gan', 'jo', 'arī', 'vispirms'], correctAnswer: 0 },
            { question: '9. “De mon point de vue” nozīmē:', options: ['No mana skatpunkta', 'No vakardienas', 'No sākuma', 'No kreisās puses'], correctAnswer: 0 },
            { question: '10. Kurš variants nozīmē “noslēgumā” ?', options: ['enfin', 'en conclusion', 'de plus', 'peut-être'], correctAnswer: 1 },
            { question: '11. “Je pense que…” nozīmē:', options: ['Es redzu, ka', 'Es domāju, ka', 'Es zinu, ka', 'Es dzirdu, ka'], correctAnswer: 1 },
            { question: '12. Kurš savienotājs palīdz pretstatīt idejas?', options: ['mais', 'et', 'aussi', 'alors'], correctAnswer: 0 }
        ]
    },
    'french-subjunctive-basics': {
        title: 'Subjonctif de base',
        subtitle: 'Pamata ievads franču subjunktīvā',
        exerciseType: 'grammar',
        questions: [
            { question: '1. Il faut que tu _____ plus tôt.', options: ['viens', 'viennes', 'venir', 'es venu'], correctAnswer: 1 },
            { question: '2. Je veux que nous _____ prêts.', options: ['sommes', 'soyons', 'être', 'avons été'], correctAnswer: 1 },
            { question: '3. Il est important qu’elle _____ ses devoirs.', options: ['fait', 'fasse', 'fera', 'a fait'], correctAnswer: 1 },
            { question: '4. Je doute qu’ils _____ aujourd’hui.', options: ['viennent', 'viendront', 'sont venus', 'venir'], correctAnswer: 0 },
            { question: '5. Il est bon que vous m’_____.', options: ['écoutez', 'écoutiez', 'avez écouté', 'écouter'], correctAnswer: 1 },
            { question: '6. Je ne pense pas qu’il _____ raison.', options: ['a', 'ait', 'avait', 'aura'], correctAnswer: 1 },
            { question: '7. Bien que ce _____ difficile, continue.', options: ['est', 'soit', 'sera', 'était'], correctAnswer: 1 },
            { question: '8. Il cherche quelqu’un qui _____ français.', options: ['parle', 'parlera', 'a parlé', 'parlait'], correctAnswer: 0 },
            { question: '9. Je suis content que vous _____ ici.', options: ['êtes', 'soyez', 'étiez', 'avez été'], correctAnswer: 1 },
            { question: '10. Il est possible que nous _____ en retard.', options: ['sommes', 'soyons', 'avons été', 'serons'], correctAnswer: 1 },
            { question: '11. Ils veulent que j’_____ davantage.', options: ['étudie', 'étudiais', 'étudierai', 'ai étudié'], correctAnswer: 0 },
            { question: '12. C’est étrange qu’Anne ne _____ pas.', options: ['vient', 'vienne', 'viendra', 'est venue'], correctAnswer: 1 }
        ]
    },
    'french-idiomatic-expressions-advanced': {
        title: 'Expressions idiomatiques',
        subtitle: 'Bieži lietotas franču idiomas un tēlaini izteicieni',
        exerciseType: 'vocabulary',
        questions: [
            { question: '1. “Avoir la tête dans les nuages” nozīmē:', options: ['Būt ļoti aizņemtam', 'Būt sapņainam un izklaidīgam', 'Būt lidmašīnā', 'Būt ļoti laimīgam'], correctAnswer: 1 },
            { question: '2. “Coûter les yeux de la tête” nozīmē:', options: ['Būt ļoti lētam', 'Būt ļoti dārgam', 'Būt bīstamam', 'Būt neērti'], correctAnswer: 1 },
            { question: '3. “Mettre les pieds dans le plat” visbiežāk nozīmē:', options: ['Kļūdīties neveikli vai pateikt ko neērtu', 'Ātri bēgt', 'Palīdzēt draugam', 'Sagatavot maltīti'], correctAnswer: 0 },
            { question: '4. “Ne pas avoir sa langue dans sa poche” nozīmē:', options: ['Runāt ļoti atklāti', 'Runāt ļoti klusi', 'Būt saslimušam', 'Būt nogurušam'], correctAnswer: 0 },
            { question: '5. “C’est du gâteau” nozīmē:', options: ['Tas ir garšīgi', 'Tas ir ļoti viegli', 'Tas ir ikdienišķi', 'Tas ir garlaicīgi'], correctAnswer: 1 },
            { question: '6. “Raconter des salades” nozīmē:', options: ['Gatavot pusdienas', 'Stāstīt muļķības', 'Dusmoties', 'Steigties'], correctAnswer: 1 },
            { question: '7. “Être crevé” nozīmē:', options: ['Būt ļoti netīram', 'Būt ļoti nogurušam', 'Būt dusmīgam', 'Būt apjukušam'], correctAnswer: 1 },
            { question: '8. “Aller droit au but” nozīmē:', options: ['Runāt tieši par būtisko', 'Iet uz veikalu', 'Pazaudēt domu', 'Strīdēties'], correctAnswer: 0 },
            { question: '9. “Jeter l’argent par les fenêtres” nozīmē:', options: ['Pārvākties', 'Tērēt ļoti daudz naudas', 'Uzņemt viesus', 'Krāt naudu'], correctAnswer: 1 },
            { question: '10. “Tomber dans les pommes” nozīmē:', options: ['Kļūdīties', 'Noģībt', 'Strādāt smagi', 'Salabot sienu'], correctAnswer: 1 },
            { question: '11. “Se bouger” nozīmē:', options: ['Saņemties un sākt darīt', 'Nopirkt mašīnu', 'Aizmigt', 'Gaidīt'], correctAnswer: 0 },
            { question: '12. “Rester bouche bée” nozīmē:', options: ['Palikt ļoti pārsteigtam', 'Nosalt aukstumā', 'Aizmirst tekstu', 'Nokrist zemē'], correctAnswer: 0 }
        ]
    },
    'french-formal-email': {
        title: 'Email formel',
        subtitle: 'Formāls stils, pieklājīgas frāzes un e-pasta struktūra',
        exerciseType: 'writing',
        questions: [
            { question: '1. Kurš sākums ir vispiemērotākais formālam e-pastam?', options: ['Salut', 'Bonjour mon ami', 'Madame, Monsieur,', 'Ça va ?'], correctAnswer: 2 },
            { question: '2. “Je vous écris pour…” nozīmē:', options: ['Es jums rakstu, lai…', 'Es gaidu jūsu vēstuli', 'Es nezinu, ko rakstīt', 'Es runāju pa telefonu'], correctAnswer: 0 },
            { question: '3. Kurš noslēgums ir formāls?', options: ['À bientôt', 'Cordialement', 'On se voit', 'Salut'], correctAnswer: 1 },
            { question: '4. “Demander des informations” nozīmē:', options: ['Nosūtīt dāvanu', 'Lūgt informāciju', 'Atcelt tikšanos', 'Mainīt tēmu'], correctAnswer: 1 },
            { question: '5. Kurš variants ir vispieklājīgākais?', options: ['Je veux ça maintenant.', 'Envoyez-le aujourd’hui.', 'Je vous serais reconnaissant(e) de votre réponse.', 'Répondez vite.'], correctAnswer: 2 },
            { question: '6. “Veuillez trouver ci-joint…” e-pastā parasti nozīmē:', options: ['Es atbildu', 'Pielikumā pievienoju', 'Es atvainojos', 'Es pārtraukšu sarunu'], correctAnswer: 1 },
            { question: '7. Kurš vietniekvārds ir formālāks?', options: ['tu', 'vous', 'nous', 'ils'], correctAnswer: 1 },
            { question: '8. “Dans l’attente de votre réponse” nozīmē:', options: ['Esmu beidzis rakstīt', 'Gaиду jūsu atbildi', 'Es jums piezvanīšu', 'Atbildiet vēlāk'], correctAnswer: 1 },
            { question: '9. Kurš temats ir piemērots formālam e-pastam?', options: ['Hey', 'Question', 'Demande d’informations sur le cours', 'Bonjour!!!'], correctAnswer: 2 },
            { question: '10. “Veuillez agréer…” visbiežāk lieto:', options: ['E-pasta sākumā', 'Starp rindkopām', 'Noslēgumā', 'Faila nosaukumā'], correctAnswer: 2 },
            { question: '11. Kurš variants ir pārāk neformāls?', options: ['Je vous écris pour confirmer…', 'Merci pour votre temps.', 'Je t’envoie ça tout de suite.', 'Je reste à votre disposition.'], correctAnswer: 2 },
            { question: '12. Formālā vēstulē vēlams:', options: ['Lietot emocijzīmes', 'Rakstīt bez sveiciena', 'Rakstīt skaidri un strukturēti', 'Izmantot tikai saīsinājumus'], correctAnswer: 2 }
        ]
    },
    'french-news-and-debate': {
        title: 'Actualités et débat',
        subtitle: 'Advanced reading, arguments and public discussion vocabulary',
        exerciseType: 'reading',
        questions: [
            { question: '1. “Selon l’article” nozīmē:', options: ['Pēc autora domām', 'Saskaņā ar rakstu', 'Raksta beigās', 'Pretēji rakstam'], correctAnswer: 1 },
            { question: '2. Kurš vārds nozīmē “pierādījumi” ?', options: ['les preuves', 'les doutes', 'les plaintes', 'les signatures'], correctAnswer: 0 },
            { question: '3. “Le sondage montre que…” nozīmē:', options: ['Intervija noliedz, ka…', 'Aptauja rāda, ka…', 'Likums pieprasa, ka…', 'Žurnālists jautā, vai…'], correctAnswer: 1 },
            { question: '4. Kurš savienotājs vislabāk ievada pretargumentu?', options: ['cependant', 'de plus', 'c’est pourquoi', 'par exemple'], correctAnswer: 0 },
            { question: '5. “La majorité des citoyens” nozīmē:', options: ['Daži pilsoņi', 'Lielākā daļa pilsoņu', 'Ārvalstu viesi', 'Visi studenti'], correctAnswer: 1 },
            { question: '6. “Prendre des mesures” visbiežāk nozīmē:', options: ['Veikt pasākumus / rīkoties', 'Mērīt galdu', 'Mainīt viedokli', 'Rakstīt atskaiti'], correctAnswer: 0 },
            { question: '7. “Le gouvernement a annoncé…” nozīmē:', options: ['Valdība paziņoja…', 'Tauta protestēja…', 'Avīze izdzēsa…', 'Pilsēta aizvērās…'], correctAnswer: 0 },
            { question: '8. “À court terme” nozīmē:', options: ['Īstermiņā', 'Ilgtermiņā', 'Bez termiņa', 'Pagājušajā gadā'], correctAnswer: 0 },
            { question: '9. Kurš vārds nozīmē “diskusija / debates” ?', options: ['le débat', 'le dessert', 'le départ', 'le détail'], correctAnswer: 0 },
            { question: '10. “La question se pose de…” nozīmē:', options: ['Jautājums par … tiek izvirzīts', 'Jautājums ir atrisināts', 'Jautājums nav svarīgs', 'Jautājums ir aizliegts'], correctAnswer: 0 },
            { question: '11. “Être favorable à” nozīmē:', options: ['Būt pret', 'Būt par / atbalstīt', 'Būt neitrālam', 'Būt apmulsušam'], correctAnswer: 1 },
            { question: '12. Labs noslēguma teikums debatēs bieži:', options: ['Atkārto galveno secinājumu', 'Ievieš pilnīgi jaunu tēmu', 'Pārtrauc sarunu bez secinājuma', 'Uzdod tikai vienu jautājumu'], correctAnswer: 0 }
        ]
    }
};
