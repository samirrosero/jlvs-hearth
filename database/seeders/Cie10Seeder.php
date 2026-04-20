<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Cie10Seeder extends Seeder
{
    public function run(): void
    {
        // Códigos CIE-10 más frecuentes en IPS de medicina general en Colombia
        $codigos = [
            // ── Enfermedades respiratorias ──────────────────────────────
            ['codigo' => 'J00',   'descripcion' => 'Rinofaringitis aguda (resfriado común)',                    'categoria' => 'J'],
            ['codigo' => 'J01.9', 'descripcion' => 'Sinusitis aguda, no especificada',                         'categoria' => 'J'],
            ['codigo' => 'J02.9', 'descripcion' => 'Faringitis aguda, no especificada',                        'categoria' => 'J'],
            ['codigo' => 'J03.9', 'descripcion' => 'Amigdalitis aguda, no especificada',                       'categoria' => 'J'],
            ['codigo' => 'J06.9', 'descripcion' => 'Infección aguda de las vías respiratorias superiores',     'categoria' => 'J'],
            ['codigo' => 'J18.9', 'descripcion' => 'Neumonía, no especificada',                                'categoria' => 'J'],
            ['codigo' => 'J20.9', 'descripcion' => 'Bronquitis aguda, no especificada',                        'categoria' => 'J'],
            ['codigo' => 'J45.9', 'descripcion' => 'Asma, no especificada',                                    'categoria' => 'J'],
            ['codigo' => 'J11.1', 'descripcion' => 'Influenza con otras manifestaciones respiratorias',        'categoria' => 'J'],
            ['codigo' => 'J30.1', 'descripcion' => 'Rinitis alérgica debida al polen',                         'categoria' => 'J'],
            ['codigo' => 'J30.4', 'descripcion' => 'Rinitis alérgica crónica',                                 'categoria' => 'J'],

            // ── Enfermedades gastrointestinales ────────────────────────
            ['codigo' => 'K29.7', 'descripcion' => 'Gastritis, no especificada',                               'categoria' => 'K'],
            ['codigo' => 'K21.0', 'descripcion' => 'Enfermedad de reflujo gastroesofágico con esofagitis',     'categoria' => 'K'],
            ['codigo' => 'K21.9', 'descripcion' => 'Enfermedad de reflujo gastroesofágico sin esofagitis',     'categoria' => 'K'],
            ['codigo' => 'K59.0', 'descripcion' => 'Estreñimiento',                                            'categoria' => 'K'],
            ['codigo' => 'K59.1', 'descripcion' => 'Diarrea funcional',                                        'categoria' => 'K'],
            ['codigo' => 'A09',   'descripcion' => 'Diarrea y gastroenteritis de presunto origen infeccioso',  'categoria' => 'A'],
            ['codigo' => 'K92.1', 'descripcion' => 'Melena',                                                   'categoria' => 'K'],
            ['codigo' => 'K57.3', 'descripcion' => 'Enfermedad diverticular del intestino grueso sin perforación','categoria' => 'K'],
            ['codigo' => 'K80.2', 'descripcion' => 'Cálculo de la vesícula biliar sin colecistitis',           'categoria' => 'K'],

            // ── Enfermedades cardiovasculares ──────────────────────────
            ['codigo' => 'I10',   'descripcion' => 'Hipertensión esencial (primaria)',                          'categoria' => 'I'],
            ['codigo' => 'I25.1', 'descripcion' => 'Enfermedad aterosclerótica del corazón',                   'categoria' => 'I'],
            ['codigo' => 'I50.9', 'descripcion' => 'Insuficiencia cardíaca, no especificada',                  'categoria' => 'I'],
            ['codigo' => 'I48',   'descripcion' => 'Fibrilación y aleteo auricular',                           'categoria' => 'I'],
            ['codigo' => 'I63.9', 'descripcion' => 'Infarto cerebral, no especificado',                        'categoria' => 'I'],
            ['codigo' => 'I20.9', 'descripcion' => 'Angina de pecho, no especificada',                         'categoria' => 'I'],

            // ── Enfermedades endocrinas y metabólicas ──────────────────
            ['codigo' => 'E11.9', 'descripcion' => 'Diabetes mellitus tipo 2 sin complicaciones',              'categoria' => 'E'],
            ['codigo' => 'E10.9', 'descripcion' => 'Diabetes mellitus tipo 1 sin complicaciones',              'categoria' => 'E'],
            ['codigo' => 'E11.6', 'descripcion' => 'Diabetes mellitus tipo 2 con otras complicaciones',        'categoria' => 'E'],
            ['codigo' => 'E03.9', 'descripcion' => 'Hipotiroidismo, no especificado',                          'categoria' => 'E'],
            ['codigo' => 'E05.9', 'descripcion' => 'Tirotoxicosis, no especificada',                           'categoria' => 'E'],
            ['codigo' => 'E78.0', 'descripcion' => 'Hipercolesterolemia pura',                                 'categoria' => 'E'],
            ['codigo' => 'E78.5', 'descripcion' => 'Hiperlipidemia, no especificada',                          'categoria' => 'E'],
            ['codigo' => 'E66.9', 'descripcion' => 'Obesidad, no especificada',                                'categoria' => 'E'],
            ['codigo' => 'E11.5', 'descripcion' => 'Diabetes mellitus tipo 2 con complicaciones circulatorias','categoria' => 'E'],

            // ── Trastornos mentales y del comportamiento ────────────────
            ['codigo' => 'F32.9', 'descripcion' => 'Episodio depresivo, no especificado',                      'categoria' => 'F'],
            ['codigo' => 'F41.1', 'descripcion' => 'Trastorno de ansiedad generalizada',                       'categoria' => 'F'],
            ['codigo' => 'F41.0', 'descripcion' => 'Trastorno de pánico',                                      'categoria' => 'F'],
            ['codigo' => 'F43.1', 'descripcion' => 'Trastorno de estrés postraumático',                        'categoria' => 'F'],
            ['codigo' => 'F10.2', 'descripcion' => 'Dependencia al alcohol',                                   'categoria' => 'F'],
            ['codigo' => 'F51.0', 'descripcion' => 'Insomnio no orgánico',                                     'categoria' => 'F'],

            // ── Sistema músculo-esquelético ────────────────────────────
            ['codigo' => 'M54.5', 'descripcion' => 'Lumbago no especificado',                                  'categoria' => 'M'],
            ['codigo' => 'M54.2', 'descripcion' => 'Cervicalgia',                                              'categoria' => 'M'],
            ['codigo' => 'M79.3', 'descripcion' => 'Paniculitis, no especificada',                             'categoria' => 'M'],
            ['codigo' => 'M25.5', 'descripcion' => 'Dolor en articulación',                                    'categoria' => 'M'],
            ['codigo' => 'M10.9', 'descripcion' => 'Gota, no especificada',                                    'categoria' => 'M'],
            ['codigo' => 'M15.9', 'descripcion' => 'Poliartrosis, no especificada',                            'categoria' => 'M'],
            ['codigo' => 'M17.1', 'descripcion' => 'Gonartrosis primaria unilateral',                          'categoria' => 'M'],
            ['codigo' => 'M47.8', 'descripcion' => 'Otras espondilosis',                                       'categoria' => 'M'],

            // ── Sistema nervioso ───────────────────────────────────────
            ['codigo' => 'G43.9', 'descripcion' => 'Migraña, no especificada',                                 'categoria' => 'G'],
            ['codigo' => 'G44.2', 'descripcion' => 'Cefalea tensional',                                        'categoria' => 'G'],
            ['codigo' => 'G40.9', 'descripcion' => 'Epilepsia, no especificada',                               'categoria' => 'G'],
            ['codigo' => 'G20',   'descripcion' => 'Enfermedad de Parkinson',                                   'categoria' => 'G'],
            ['codigo' => 'G35',   'descripcion' => 'Esclerosis múltiple',                                       'categoria' => 'G'],
            ['codigo' => 'G47.0', 'descripcion' => 'Trastornos del inicio y del mantenimiento del sueño',      'categoria' => 'G'],

            // ── Enfermedades de la piel ────────────────────────────────
            ['codigo' => 'L20.9', 'descripcion' => 'Dermatitis atópica, no especificada',                      'categoria' => 'L'],
            ['codigo' => 'L30.9', 'descripcion' => 'Dermatitis, no especificada',                              'categoria' => 'L'],
            ['codigo' => 'L40.0', 'descripcion' => 'Psoriasis vulgar',                                         'categoria' => 'L'],
            ['codigo' => 'L50.9', 'descripcion' => 'Urticaria, no especificada',                               'categoria' => 'L'],
            ['codigo' => 'B02.9', 'descripcion' => 'Zóster sin complicaciones',                                 'categoria' => 'B'],
            ['codigo' => 'L03.9', 'descripcion' => 'Celulitis, no especificada',                               'categoria' => 'L'],

            // ── Enfermedades genitourinarias ───────────────────────────
            ['codigo' => 'N39.0', 'descripcion' => 'Infección de vías urinarias, sitio no especificado',       'categoria' => 'N'],
            ['codigo' => 'N40',   'descripcion' => 'Hiperplasia de la próstata',                               'categoria' => 'N'],
            ['codigo' => 'N92.0', 'descripcion' => 'Menstruación excesiva y frecuente con ciclo regular',      'categoria' => 'N'],
            ['codigo' => 'N94.6', 'descripcion' => 'Dismenorrea, no especificada',                             'categoria' => 'N'],
            ['codigo' => 'N20.0', 'descripcion' => 'Cálculo del riñón',                                        'categoria' => 'N'],
            ['codigo' => 'N18.9', 'descripcion' => 'Insuficiencia renal crónica, no especificada',             'categoria' => 'N'],

            // ── Embarazo y parto ───────────────────────────────────────
            ['codigo' => 'Z34.0', 'descripcion' => 'Supervisión de embarazo normal, primer trimestre',         'categoria' => 'Z'],
            ['codigo' => 'Z34.1', 'descripcion' => 'Supervisión de embarazo normal, segundo trimestre',        'categoria' => 'Z'],
            ['codigo' => 'Z34.2', 'descripcion' => 'Supervisión de embarazo normal, tercer trimestre',         'categoria' => 'Z'],
            ['codigo' => 'O10.0', 'descripcion' => 'Hipertensión esencial preexistente complicando embarazo',  'categoria' => 'O'],
            ['codigo' => 'O24.4', 'descripcion' => 'Diabetes mellitus que surge durante el embarazo',          'categoria' => 'O'],

            // ── Infecciones ────────────────────────────────────────────
            ['codigo' => 'A90',   'descripcion' => 'Dengue clásico',                                           'categoria' => 'A'],
            ['codigo' => 'A91',   'descripcion' => 'Fiebre hemorrágica debida al virus del dengue',            'categoria' => 'A'],
            ['codigo' => 'A97.9', 'descripcion' => 'Dengue, no especificado',                                  'categoria' => 'A'],
            ['codigo' => 'B34.9', 'descripcion' => 'Infección viral, no especificada',                         'categoria' => 'B'],
            ['codigo' => 'A49.0', 'descripcion' => 'Infección estafilocócica, sitio no especificado',          'categoria' => 'A'],
            ['codigo' => 'A49.1', 'descripcion' => 'Infección estreptocócica, sitio no especificado',          'categoria' => 'A'],
            ['codigo' => 'B50.9', 'descripcion' => 'Paludismo por Plasmodium falciparum, no especificado',     'categoria' => 'B'],
            ['codigo' => 'U07.1', 'descripcion' => 'COVID-19, virus identificado',                             'categoria' => 'U'],
            ['codigo' => 'U07.2', 'descripcion' => 'COVID-19, virus no identificado',                          'categoria' => 'U'],

            // ── Ojos y oídos ───────────────────────────────────────────
            ['codigo' => 'H10.9', 'descripcion' => 'Conjuntivitis, no especificada',                           'categoria' => 'H'],
            ['codigo' => 'H52.4', 'descripcion' => 'Presbiopía',                                               'categoria' => 'H'],
            ['codigo' => 'H65.9', 'descripcion' => 'Otitis media no supurativa, no especificada',              'categoria' => 'H'],
            ['codigo' => 'H66.9', 'descripcion' => 'Otitis media, no especificada',                            'categoria' => 'H'],
            ['codigo' => 'H81.0', 'descripcion' => 'Enfermedad de Ménière',                                    'categoria' => 'H'],

            // ── Consultas de control y preventivas ─────────────────────
            ['codigo' => 'Z00.0', 'descripcion' => 'Examen médico general',                                    'categoria' => 'Z'],
            ['codigo' => 'Z00.1', 'descripcion' => 'Control de salud rutinario del niño',                      'categoria' => 'Z'],
            ['codigo' => 'Z13.6', 'descripcion' => 'Examen especial de detección de enfermedades cardiovasculares','categoria' => 'Z'],
            ['codigo' => 'Z71.3', 'descripcion' => 'Consulta dietética',                                       'categoria' => 'Z'],
            ['codigo' => 'Z30.0', 'descripcion' => 'Consejo y asesoramiento sobre anticoncepción',             'categoria' => 'Z'],
            ['codigo' => 'Z82.4', 'descripcion' => 'Historia familiar de cardiopatía isquémica',               'categoria' => 'Z'],

            // ── Traumatismos frecuentes ────────────────────────────────
            ['codigo' => 'S00.9', 'descripcion' => 'Traumatismo superficial de la cabeza, no especificado',   'categoria' => 'S'],
            ['codigo' => 'S60.9', 'descripcion' => 'Traumatismo superficial de la muñeca y la mano',          'categoria' => 'S'],
            ['codigo' => 'S80.9', 'descripcion' => 'Traumatismo superficial de la pierna, no especificado',   'categoria' => 'S'],
            ['codigo' => 'S93.4', 'descripcion' => 'Esguince y torcedura del tobillo',                        'categoria' => 'S'],
            ['codigo' => 'T14.9', 'descripcion' => 'Traumatismo no especificado',                              'categoria' => 'T'],

            // ── Síntomas y signos generales (R) ───────────────────────
            ['codigo' => 'R05',   'descripcion' => 'Tos',                                                      'categoria' => 'R'],
            ['codigo' => 'R05.1', 'descripcion' => 'Tos aguda',                                                'categoria' => 'R'],
            ['codigo' => 'R05.4', 'descripcion' => 'Tos crónica',                                              'categoria' => 'R'],
            ['codigo' => 'R05.9', 'descripcion' => 'Tos no especificada',                                      'categoria' => 'R'],
            ['codigo' => 'R50.9', 'descripcion' => 'Fiebre, no especificada',                                  'categoria' => 'R'],
            ['codigo' => 'R51',   'descripcion' => 'Cefalea',                                                  'categoria' => 'R'],
            ['codigo' => 'R10.4', 'descripcion' => 'Dolor abdominal, no especificado',                        'categoria' => 'R'],
            ['codigo' => 'R06.0', 'descripcion' => 'Disnea',                                                   'categoria' => 'R'],
            ['codigo' => 'R06.2', 'descripcion' => 'Sibilancias',                                              'categoria' => 'R'],
            ['codigo' => 'R07.9', 'descripcion' => 'Dolor en el pecho, no especificado',                      'categoria' => 'R'],
            ['codigo' => 'R11',   'descripcion' => 'Náusea y vómito',                                          'categoria' => 'R'],
            ['codigo' => 'R00.0', 'descripcion' => 'Taquicardia, no especificada',                             'categoria' => 'R'],
            ['codigo' => 'R03.0', 'descripcion' => 'Presión arterial elevada',                                 'categoria' => 'R'],
            ['codigo' => 'R42',   'descripcion' => 'Mareo y desvanecimiento',                                  'categoria' => 'R'],
            ['codigo' => 'R53',   'descripcion' => 'Malestar y fatiga',                                        'categoria' => 'R'],
            ['codigo' => 'R68.9', 'descripcion' => 'Síntoma general, no especificado',                        'categoria' => 'R'],
        ];

        DB::table('cie10')->insertOrIgnore($codigos);
    }
}
