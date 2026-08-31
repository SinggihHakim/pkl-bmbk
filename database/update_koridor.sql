-- ============================================================
-- SQL Update Koridor Ruas Jalan (22 Koridor)
-- ============================================================

USE `stripmap_db`;

-- KORIDOR 1
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 1' WHERE 
   `nama_ruas` LIKE '%Pagar Alam%Kalianda%' 
OR `nama_ruas` LIKE '%Kalianda%Kunyir%Gayam%' 
OR `nama_ruas` LIKE '%Gayam%Ketapang%' 
OR `nama_ruas` LIKE '%Sidomulyo%Belimbing Sari%' 
OR `nama_ruas` LIKE '%Belimbing Sari%Jabung%' 
OR `nama_ruas` LIKE '%Jabung%Labuhan Maringgai%';

-- KORIDOR 2
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 2' WHERE 
   `nama_ruas` LIKE '%A.Yani%Metro%' OR `nama_ruas` LIKE '%A. Yani%Metro%'
OR `nama_ruas` LIKE '%Metro%Tanjung Kari%' 
OR `nama_ruas` LIKE '%Nyampir%Tanjung Kari%' 
OR `nama_ruas` LIKE '%Tanjung Kari%Pugung Raharjo%' 
OR `nama_ruas` LIKE '%Pugung Raharjo%Jabung%';

-- KORIDOR 3
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 3' WHERE 
   `nama_ruas` LIKE '%Ryacudu%' 
OR `nama_ruas` LIKE '%Korpri%Sukadamai%' 
OR `nama_ruas` LIKE '%Sukadamai%Kibang%' 
OR `nama_ruas` LIKE '%Budi Utomo%Metro%' 
OR `nama_ruas` LIKE '%Soekarno%Hatta%Metro%' 
OR `nama_ruas` LIKE '%Korpri%Purwotani%' 
OR `nama_ruas` LIKE '%Veteran%Metro%' 
OR `nama_ruas` LIKE '%Pattimura%Metro%' 
OR `nama_ruas` LIKE '%Metro%Kota Gajah%';

-- KORIDOR 4
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 4' WHERE 
   `nama_ruas` LIKE '%Kota Gajah%Randu%' 
OR `nama_ruas` LIKE '%Randu%Seputih Surabaya%' 
OR `nama_ruas` LIKE '%Seputih Surabaya%Sadewa%';

-- KORIDOR 5
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 5' WHERE 
   `nama_ruas` LIKE '%Gunung Sugih%Kota Gajah%' 
OR `nama_ruas` LIKE '%Kota Gajah%Gedong Dalem%' 
OR `nama_ruas` LIKE '%Bandar Jaya%Mandala%';

-- KORIDOR 6
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 6' WHERE 
   `nama_ruas` LIKE '%Kalirejo%Bangunrejo%' 
OR `nama_ruas` LIKE '%Bangunrejo%Wates%' 
OR `nama_ruas` LIKE '%Wates%Metro%' 
OR `nama_ruas` LIKE '%Brigjen Katamso%Metro%';

-- KORIDOR 7
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 7' WHERE 
   `nama_ruas` LIKE '%Gunung Sugih%Padang Ratu%' 
OR `nama_ruas` LIKE '%Padang Ratu%Pekurun Udik%' 
OR `nama_ruas` LIKE '%Pekurun Udik%Aji Kagungan%';

-- KORIDOR 8
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 8' WHERE 
   `nama_ruas` LIKE '%Padang Ratu%Kalirejo%' 
OR `nama_ruas` LIKE '%Kalirejo%Pringsewu%' 
OR `nama_ruas` LIKE '%Pringsewu%Pardasuka%' 
OR `nama_ruas` LIKE '%Pardasuka%Sukamara%' 
OR `nama_ruas` LIKE '%Sukamara%Kuripan%';

-- KORIDOR 9
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 9' WHERE 
   `nama_ruas` LIKE '%Branti%Gedong Tataan%' 
OR `nama_ruas` LIKE '%Gedong Tataan%Kedondong%' 
OR `nama_ruas` LIKE '%Kedondong%Pardasuka%' 
OR `nama_ruas` LIKE '%Padang Cermin%Kedondong%';

-- KORIDOR 10
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 10' WHERE 
   `nama_ruas` LIKE '%Tenggiri%' 
OR `nama_ruas` LIKE '%R.E.Martadinata%' OR `nama_ruas` LIKE '%RE Martadinata%'
OR `nama_ruas` LIKE '%Lempasing%Padang Cermin%' 
OR `nama_ruas` LIKE '%Padang Cermin%Teluk Kiluan%';

-- KORIDOR 11
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 11' WHERE 
   `nama_ruas` LIKE '%Teluk Kiluan%Umbar%' 
OR `nama_ruas` LIKE '%Umbar%Putih Doh%' 
OR `nama_ruas` LIKE '%Putih Doh%Kuripan%' 
OR `nama_ruas` LIKE '%Kuripan%Kota Agung%';

-- KORIDOR 12
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 12' WHERE 
   `nama_ruas` LIKE '%Pekon Balak%Suoh%' 
OR `nama_ruas` LIKE '%Suoh%Blok 9%' 
OR `nama_ruas` LIKE '%Blok 9%Sanggi%' 
OR `nama_ruas` LIKE '%Raden Intan%Liwa%' 
OR `nama_ruas` LIKE '%Liwa%Bts.Sumsel%' OR `nama_ruas` LIKE '%Liwa%Sumsel%'
OR `nama_ruas` LIKE '%Adam Malik%Krui%' 
OR `nama_ruas` LIKE '%Krui%Pekon Serai%' 
OR `nama_ruas` LIKE '%Kota Jawa%Kampung Baru%';

-- KORIDOR 13
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 13' WHERE 
   `nama_ruas` LIKE '%Tekad%Batutegi%' 
OR `nama_ruas` LIKE '%Talang Padang%Ngarip%' 
OR `nama_ruas` LIKE '%Ngarip%Ulu Semong%' 
OR `nama_ruas` LIKE '%Ulu Semong%Trimulyo%' 
OR `nama_ruas` LIKE '%Trimulyo%Bungin%Tugu Sari%';

-- KORIDOR 14
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 14' WHERE 
   `nama_ruas` LIKE '%Abung Raya Brt%' OR `nama_ruas` LIKE '%Abung Raya Barat%'
OR `nama_ruas` LIKE '%Abung Raya Tmr%' OR `nama_ruas` LIKE '%Abung Raya Timur%'
OR `nama_ruas` LIKE '%Kotabumi%Bandar Abung%' 
OR `nama_ruas` LIKE '%Bandar Abung%Bandar Sakti%' 
OR `nama_ruas` LIKE '%Bandar Sakti%Daya Murni%' 
OR `nama_ruas` LIKE '%Daya Murni%Gunung Batin%' 
OR `nama_ruas` LIKE '%Bandar Abung%Tujok%';

-- KORIDOR 15
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 15' WHERE 
   `nama_ruas` LIKE '%Negara Ratu%Tujok%' 
OR `nama_ruas` LIKE '%Tujok%Panaragan Jaya%' 
OR `nama_ruas` LIKE '%Panaragan Jaya%Panaragan%';

-- KORIDOR 16
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 16' WHERE 
   `nama_ruas` LIKE '%Kotabumi%Ketapang%' 
OR `nama_ruas` LIKE '%Ketapang%Negara Ratu%' 
OR `nama_ruas` LIKE '%Negara Ratu%Gunung Betuah%' 
OR `nama_ruas` LIKE '%Gunung Betuah%Gunung Labuhan%';

-- KORIDOR 17
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 17' WHERE 
   `nama_ruas` LIKE '%Sp.Empat%Kasui%' OR `nama_ruas` LIKE '%Simpang Empat%Kasui%'
OR `nama_ruas` LIKE '%Kasui%Air Ringkih%';

-- KORIDOR 18
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 18' WHERE 
   `nama_ruas` LIKE '%Empat%Blambangan Umpu%' 
OR `nama_ruas` LIKE '%Blambangan Umpu%Sri Rejeki%' 
OR `nama_ruas` LIKE '%Sri Rejeki%Pakuan Ratu%' 
OR `nama_ruas` LIKE '%Pakuan Ratu%Bumiharjo%' 
OR `nama_ruas` LIKE '%Bumiharjo%Way Tuba%';

-- KORIDOR 19
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 19' WHERE 
   `nama_ruas` LIKE '%Negara Ratu%Soponyono%' 
OR `nama_ruas` LIKE '%Soponyono%Serupa Indah%' 
OR `nama_ruas` LIKE '%Serupa Indah%Pakuan Ratu%' 
OR `nama_ruas` LIKE '%Serupa Indah%Tajab%';

-- KORIDOR 20
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 20' WHERE 
   `nama_ruas` LIKE '%Gunung Sakti%Menggala%' 
OR `nama_ruas` LIKE '%Bujung Tenuk%Penumangan%' 
OR `nama_ruas` LIKE '%Penumangan%Tegal Mukti%' 
OR `nama_ruas` LIKE '%Tegal Mukti%Tajab%';

-- KORIDOR 21
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 21' WHERE 
   `nama_ruas` LIKE '%Tajab%Adijaya%' 
OR `nama_ruas` LIKE '%Adijaya%Tulung Randu%' 
OR `nama_ruas` LIKE '%Penumangan%Unit VI%';

-- KORIDOR 22
UPDATE `ruas_jalan` SET `koridor` = 'KORIDOR 22' WHERE 
   `nama_ruas` LIKE '%Unit VIII%Gedong Aji%' 
OR `nama_ruas` LIKE '%Gedong Aji%Umbul Mesir%' 
OR `nama_ruas` LIKE '%Pematang%Brabasan%' 
OR `nama_ruas` LIKE '%Brabasan%Wiralaga%';
