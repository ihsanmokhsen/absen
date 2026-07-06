<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeesByBidang = [
            'SEKRETARIAT' => [
                'Johny Ericson Ataupah, SP., MM',
                'Drs. Yoseph Florianus Napal, MM',
                'Aprianus Aryantho Rondak, S.STP',
                'Yulius B. Lico, S.Sos',
                'Lori N. Sioh, S.Sos',
                'Kristoforus R. Hayong, S.Kom., MM',
                'Januarius J. B. Banase, S.Sos',
                'Anselma Magdalena, S.Sos',
                'Markus Radja',
                'Sandy A. J. L. Pranadjaya, SH',
                'Vinsensius N. T. Atidja',
                'Alfred Marlison Mulle',
                'Marselinus Tahu Tetik',
                'Banni Bangngu Gada, SE',
                'Anita Damaris Pello, SE',
                'Sesilia Yosephina Pati, SE',
                'Indah Jayanti, S.STP',
                'Muhammad U. Ratu Loli',
                'Nurmi Wahyuni, A.Md',
                'Yefrid Nabuasa',
                'Eugene Cornelis, SE',
                'Dessy Nathaly Leo, SE',
                'Muhammad I. H. Mokhsen, S.Kom',
                'Inge Putri Dimamesa',
                'Lusiana Virgine C. Angkur, S.Kom',
                'Donna R. J. Donni, S.Kom',
                'Mariano Djogo Lainurak, S.Fil',
                'Harvido Aquino Rubian, SH',
                'Aris E. O. Laikopan, S.AB',
                'Lidwina Maria Ellyana, SE',
                'Novalia I. Putri Acry, S.IP.',
                'Maria Poncowati Handayani, SH',
                'Yuliana Lipat Purab, S.Kom',
                'Magdalena B. Tapobali, S.Tr.I.Kom',
                'Rina Juinda Laiskodat, SP',
                'Hasabya Eliaser Boling, S.Tr.M',
                'Leonardi M. N. Kulas, SE.',
                'Joachim A. K. Ulin, SM',
                'Johstin Adeliana Metkono, A.Md',
                'Agnes Snaver Asan, A.Md',
                'Merriyanti S. Koba',
                'Octovianus A. Kaha',
                'Mario M. Aloysius Laurensius',
                'Ramona Samiun',
                'Nelson Peren',
                'Daud Telnoni',
                'Jacobus Tonael',
                'Christianus Ronge',
                'Adi Arifin Johan Tefa',
                'Petrus Fredyk Kalumbang, A.Md',
                'Marselinus Nainoe',
            ],
            'PENDAPATAN 1' => [
                'Oktavianus Mare, SS',
                'Maria Y. D. Hendrayani, S.ST',
                'France F. G. Naibobe, S.STP., MM',
                'Yohanes Nahak, S.Kom., MT',
                'Maria Ivoni T. Lina, ST',
                'Marryquest B. Edison, SE., MM',
                'Luh Astiti Ariati, S.Ak',
                'Benny E. B. Foenay',
                'Arsena Marshal Adu, S.Sos',
                'Vinitria Cornelia Manehat, S.Kom',
                'Louis Adrian Lonis, S.Kom',
                'Felipus Yanuar K. A. Muni, S.Kom',
                'Jessica S. Ang Djadi, S.Kom',
                'Andreas A. Sabi, S.STP',
                'Adriana Medah, SH.',
                'Yuliana M. P. Jong Joko, S.Sos',
                'Kenny Adrian M. Langgar, ST',
                'Jovian Raymond Lico, SE',
                'Maria Elisabeth Nay, ST',
                'Lidya Banunaek, SE',
                'Aritanto S. Hayong, SE',
                'Christina M. Triyanti, SE',
                'Abednego D. Imanuel Ena',
                'Yuliana M. G. Mau',
            ],
            'PENDAPATAN 2' => [
                'Lusia Fransiska Tiwe, ST',
                'Karel Eben Umbu Kaballu, S.STP',
                'Rolinda Inneke Foenay, S.Kom',
                'Kefi Z. M. Taku Bessi, SE., MM',
                'Antonius Fuka, S.Sos',
                'Fransiska Devy S. Lorang, S.IP',
                'Erich Alfaredo Boro, SE',
                'Adelbertus Lamahoda, A.Md',
                'Melkisedek Koa, A.Md',
                'Debora Kondo',
                'Frangky T. Mone',
                'Novyanti Arlyn Mau, SE',
                'Celine Narumi Lomanledo, S.AB',
                'Dessy I. Mone, S.Sos.',
                'Yustinus Bani',
            ],
            'ASET 1' => [
                'Sany H. Tetmilay. SEAk., M.Acc',
                'Firminus Kapitan, S.STP',
                'Lambertus Buda, S.Sos',
                'Jacobus Makin, ST., M.Ec.Dev.',
                'Maryam Aras, S.Sos',
                'Marcel F. Elim, ST',
                'Sandra A. Suratama, SE., MM',
                'Alfret D. I. Tunliu, S.Kom',
                'Florinda da Costa Soares',
                'Nadya Centini H. Koso, S.Tr.Ak',
                'Patrisiana N. Kire Herin, S.Tr.Ak',
                'Miryanti Kewahe Tokan, S.Pd',
                'Andreas H. Belang, S.Kom',
                'Irenius Angky Amaina, SH',
                'Eka Triyanti Lehilaka, SE',
                'Olga Adhe F. Pandie',
                'Marianus Ribu Kelen',
                'Ardimelek Lona',
            ],
            'ASET 2' => [
                'Drs. Dominikus D. Payong, MA',
                'Natalia Th. F. Saba, S.Sos., MM',
                'Yus Ressie, SH',
                'Maria Indah Imakulata, S.Sos',
                'Isidorus C. Tolan Pari, SE., MM',
                'Hilaria de Jesus Mendes, SH',
                'Jori Bawa, SE',
                'Ariesta Theresia Tokan, SE',
                'Novita Adris Passu, A.Md',
                'Semapritu Ndaomanu',
                'Don G. E. da Costa, ST., MM',
                'Dominggus Wila Huky, S.AB',
                'Muhammad Ichsan Eke, SH',
                'Alfred Malaikari, SH',
                'Anselmus D. Sanga, S.Kom',
                'Fransiskus Alexander Pawe, S.IP',
                'Astrid Katty B. Koreh',
                'Yahya Libing',
                'Eben C. Foenay',
                'Vega N. Mudin',
            ],
        ];

        $activeNames = [];
        $sortOrder = 1;
        $pppkRanges = [
            [26, 51],
            [66, 75],
            [87, 90],
            [102, 108],
            [119, 128],
        ];

        foreach ($employeesByBidang as $bidang => $names) {
            foreach ($names as $name) {
                $activeNames[] = $name;
                $isPppk = collect($pppkRanges)
                    ->contains(fn (array $range) => $sortOrder >= $range[0] && $sortOrder <= $range[1]);

                Employee::updateOrCreate(
                    [
                        'name' => $name,
                        'bidang' => $bidang,
                    ],
                    [
                        'nip' => null,
                        'sort_order' => $sortOrder,
                        'is_pppk' => $isPppk,
                        'is_active' => true,
                    ],
                );

                $sortOrder++;
            }
        }

        Employee::whereNotIn('name', $activeNames)->update(['is_active' => false]);
    }
}
