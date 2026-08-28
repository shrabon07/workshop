-- =====================================================================
--  AURORA CYBER — Web Development Agency
--  Complete MySQL/MariaDB schema + seed data (UTF-8)
--  Import via phpMyAdmin or run the installer: http://localhost/workshop/install.php
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- USERS (admins + customers)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120)  NOT NULL,
  `email`      VARCHAR(190)  NOT NULL,
  `password`   VARCHAR(255)  NOT NULL,
  `phone`      VARCHAR(30)   NULL DEFAULT NULL,
  `role`       ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- VERIFICATION STATUS  (email / whatsapp + admin override tick)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `verification_status` (
  `id`                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`              INT UNSIGNED  NOT NULL,
  `email_verified`       TINYINT(1)    NOT NULL DEFAULT 0,
  `whatsapp_verified`    TINYINT(1)    NOT NULL DEFAULT 0,
  `email_verified_at`    DATETIME      NULL DEFAULT NULL,
  `whatsapp_verified_at` DATETIME      NULL DEFAULT NULL,
  `admin_override`       ENUM('none','red','grey','green') NOT NULL DEFAULT 'none',
  `updated_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ver_user` (`user_id`),
  CONSTRAINT `fk_ver_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SERVICE CATEGORIES
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name_en`    VARCHAR(120)  NOT NULL,
  `name_bn`    VARCHAR(120)  NOT NULL,
  `slug`       VARCHAR(140)  NOT NULL,
  `sort_order` INT           NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- SERVICES
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `category_id`   INT UNSIGNED   NULL DEFAULT NULL,
  `title_en`      VARCHAR(160)   NOT NULL,
  `title_bn`      VARCHAR(160)   NOT NULL,
  `slug`          VARCHAR(190)   NOT NULL,
  `short_desc_en` TEXT           NULL,
  `short_desc_bn` TEXT           NULL,
  `full_desc_en`  LONGTEXT       NULL,
  `full_desc_bn`  LONGTEXT       NULL,
  `price`         DECIMAL(12,2)  NOT NULL DEFAULT 0,
  `price_label`   VARCHAR(60)    NOT NULL DEFAULT 'Starts from',
  `features_en`   JSON           NULL,
  `features_bn`   JSON           NULL,
  `thumbnail`     VARCHAR(255)   NULL,
  `gallery`       JSON           NULL,
  `status`        ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
  `is_featured`   TINYINT(1)     NOT NULL DEFAULT 0,
  `sort_order`    INT            NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_slug` (`slug`),
  KEY `k_services_cat` (`category_id`),
  KEY `k_services_status_sort` (`status`,`sort_order`),
  CONSTRAINT `fk_services_cat` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- ORDERS (custom orders from guests + logged-in customers)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED  NULL DEFAULT NULL,
  `service_id`   INT UNSIGNED  NULL DEFAULT NULL,
  `name`         VARCHAR(120)  NOT NULL,
  `email`        VARCHAR(190)  NOT NULL,
  `phone`        VARCHAR(30)   NULL DEFAULT NULL,
  `project_type` VARCHAR(120)  NULL DEFAULT NULL,
  `budget`       DECIMAL(12,2) NULL DEFAULT NULL,
  `details`      TEXT          NULL,
  `status`       ENUM('pending','in_progress','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `k_orders_user` (`user_id`),
  KEY `k_orders_service` (`service_id`),
  KEY `k_orders_status` (`status`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_orders_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- CHAT SESSIONS (live chat widget — bot / admin takeover)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chat_sessions` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `session_token` VARCHAR(64)   NOT NULL,
  `user_id`       INT UNSIGNED  NULL DEFAULT NULL,
  `guest_name`    VARCHAR(120)  NULL DEFAULT NULL,
  `bot_mode`      TINYINT(1)    NOT NULL DEFAULT 1,
  `admin_taken`   TINYINT(1)    NOT NULL DEFAULT 0,
  `status`        ENUM('open','closed') NOT NULL DEFAULT 'open',
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chat_token` (`session_token`),
  KEY `k_chat_status` (`status`,`updated_at`),
  CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- CHAT MESSAGES
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `chat_id`    INT UNSIGNED    NOT NULL,
  `sender`     ENUM('guest','bot','admin') NOT NULL DEFAULT 'guest',
  `message`    TEXT            NOT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `k_msg_chat` (`chat_id`,`id`),
  CONSTRAINT `fk_msg_chat` FOREIGN KEY (`chat_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- OTP CODES (email + whatsapp verification)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `otp_codes` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `channel`    ENUM('email','whatsapp') NOT NULL,
  `purpose`    VARCHAR(40)   NOT NULL DEFAULT 'verify',
  `code_hash`  VARCHAR(64)   NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `k_otp_user_channel` (`user_id`,`channel`,`used`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- TRANSLATIONS  (server JSON overrides for the JS dictionary)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `translations` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dict_key`   VARCHAR(190) NOT NULL,
  `en`         TEXT         NOT NULL,
  `bn`         TEXT         NOT NULL,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tr_key` (`dict_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  SEED DATA
-- =====================================================================

-- ---- users : passwords shown are the demo credentials ------------------
-- admin@auroracyber.com / admin123
-- customer@demo.com    / customer123   (red — nothing verified)
-- verified@demo.com    / verified123   (grey — email only)
-- full@demo.com        / full123       (green — email + whatsapp)
INSERT INTO `users` (`id`,`name`,`email`,`password`,`phone`,`role`) VALUES
(1,'Aurora Admin','admin@auroracyber.com','$2y$10$7pv1TUcyVVBCpwEY0RiXT.4u7WoNzcfvsJJlwEPTUcAxcMsTa04te','+8801712345678','admin'),
(2,'Rahim Karim','customer@demo.com','$2y$10$pXKuiK.W0xmpeiLAh6sQoOkPDZpmJhzGrPcZJFBcCkfFHTlim/e9K','01711223344','customer'),
(3,'Tasnim Binte Ali','verified@demo.com','$2y$10$e3a.Wk3yL.GNE6yeBh/BvOe2NXJmHfFMn6QnrMobqImwVtm3KqQ3W','01822334455','customer'),
(4,'Nafis Hasan','full@demo.com','$2y$10$OzGifd3F/jpKOPiEkG1/WevoeS0cwl4JiurJjDZSLTdKtBWZYnfuG','01933445566','customer');

INSERT INTO `verification_status` (`user_id`,`email_verified`,`whatsapp_verified`,`email_verified_at`,`whatsapp_verified_at`,`admin_override`) VALUES
(1,0,0,NULL,NULL,'none'),
(2,0,0,NULL,NULL,'none'),
(3,1,0,'2026-08-10 10:00:00',NULL,'none'),
(4,1,1,'2026-08-12 10:00:00','2026-08-12 10:05:00','none');

-- ---- service categories -------------------------------------------------
INSERT INTO `service_categories` (`id`,`name_en`,`name_bn`,`slug`,`sort_order`) VALUES
(1,'E-Commerce','ই-কমার্স','ecommerce',1),
(2,'SaaS','সাস','saas',2),
(3,'Portfolio','পোর্টফোলিও','portfolio',3),
(4,'Landing Page','ল্যান্ডিং পেজ','landing-page',4),
(5,'Custom','কাস্টম','custom',5);

-- ---- services ----------------------------------------------------------
INSERT INTO `services`
(`id`,`category_id`,`title_en`,`title_bn`,`slug`,`short_desc_en`,`short_desc_bn`,`full_desc_en`,`full_desc_bn`,
 `price`,`price_label`,`features_en`,`features_bn`,`status`,`is_featured`,`sort_order`) VALUES
(1,1,'E-Commerce Store','ই-কমার্স ওয়েবসাইট','ecommerce-store',
 'A complete online store with product management, cart, checkout and bKash/Nagad payment integration.',
 'প্রোডাক্ট ম্যানেজমেন্ট, কার্ট, চেকআউট এবং বিকাশ/নগদ পেমেন্টসহ সম্পূর্ণ অনলাইন স্টোর।',
 'Launch a revenue-ready online shop built for Bangladesh. We design a fast, mobile-first storefront with product catalog, secure checkout, order tracking and bKash / Nagad / SSLCommerz payment gateways, plus an easy-to-use admin to manage inventory, offers and customers. Your store ships with SEO, analytics and a 30-day free support window.',
 'বাংলাদেশের বাজারের জন্য তৈরি করুন একটি রেডি-টু-সেল অনলাইন স্টোর। আমাদের দল ডিজাইন করে দেবে দ্রুত ও মোবাইল-ফার্স্ট স্টোরফ্রন্ট, প্রোডাক্ট ক্যাটালগ, সিকিউর চেকআউট, অর্ডার ট্র্যাকিং এবং বিকাশ/নগদ/এসএসএলকমার্জ পেমেন্ট গেটওয়ে। ইনভেন্টরি, অফার ও গ্রাহক ব্যবস্থাপনার জন্য থাকছে সহজ অ্যাডমিন প্যানেল। SEO, অ্যানালিটিক্স এবং ৩০ দিনের ফ্রি সাপোর্ট সবই অন্তর্ভুক্ত।',
 25000.00,'Starts from',
 JSON_ARRAY('Product catalog','Cart & checkout','bKash / Nagad / SSLCommerz','Order tracking','Responsive design'),
 JSON_ARRAY('প্রোডাক্ট ক্যাটালগ','কার্ট ও চেকআউট','বিকাশ / নগদ / এসএসএলকমার্জ','অর্ডার ট্র্যাকিং','রেসপনসিভ ডিজাইন'),
 'active',1,1),
(2,2,'SaaS Platform','সাস প্ল্যাটফর্ম','saas-platform',
 'A full subscription-as-a-service platform with roles, billing, dashboards and REST APIs.',
 'রোল, বিলিং, ড্যাশবোর্ড এবং REST API সহ একটি সম্পূর্ণ সাবস্ক্রিপশন-অ্যাজ-আ-সার্ভিস প্ল্যাটফর্ম।',
 'From MVP to scale — we architect SaaS products with multi-user roles, subscription billing, admin dashboards and clean REST APIs. You get a production-ready foundation with database design, caching, and deployment guidance so your product stays fast as it grows.',
 'এমভিপি থেকে স্কেল পর্যন্ত — আমরা ডিজাইন করি মাল্টি-ইউজার রোল, সাবস্ক্রিপশন বিলিং, অ্যাডমিন ড্যাশবোর্ড ও পরিষ্কার REST API সহ SaaS প্রোডাক্ট। ডেটাবেস ডিজাইন, ক্যাশিং ও ডিপ্লয়মেন্ট গাইডেন্সসহ একটি প্রোডাকশন-রেডি ফাউন্ডেশন পাবেন, যা গ্রাহক বাড়ার সঙ্গেও দ্রুত থাকবে।',
 60000.00,'Starts from',
 JSON_ARRAY('User roles & permissions','Subscription billing','Admin dashboard','REST API','Analytics'),
 JSON_ARRAY('ইউজার রোল ও পারমিশন','সাবস্ক্রিপশন বিলিং','অ্যাডমিন ড্যাশবোর্ড','REST API','অ্যানালিটিক্স'),
 'active',1,2),
(3,3,'Portfolio Website','পোর্টফোলিও ওয়েবসাইট','portfolio-website',
 'A stunning personal portfolio that attracts clients and showcases your best work.',
 'একটি আকর্ষণীয় ব্যক্তিগত পোর্টফোলিও, যা ক্লায়েন্ট আকর্ষণ করবে এবং আপনার সেরা কাজ তুলে ধরবে।',
 'Every creative deserves a digital business card that converts. We craft a sleek, gallery-driven portfolio with a contact form, resume page, fast loading and subtle scroll animations — the perfect first impression for freelancers, designers and agencies.',
 'প্রতিটি ক্রিয়েটিভ প্রফেশনালের জন্য একটি ডিজিটাল বিজনেস কার্ড। আমরা বানাই স্লিক, গ্যালারি-ভিত্তিক পোর্টফোলিও — যোগাযোগ ফর্ম, রিজিউম পেজ, দ্রুত লোডিং ও হালকা স্ক্রল অ্যানিমেশনসহ। ফ্রিল্যান্সার, ডিজাইনার ও এজেন্সিদের জন্য নিখুঁত প্রথম ইমপ্রেশন।',
 8000.00,'Fixed',
 JSON_ARRAY('Gallery & case studies','Contact form','Resume / CV','Fast loading','Scroll animations'),
 JSON_ARRAY('গ্যালারি ও কেস স্টাডি','যোগাযোগ ফর্ম','রিজিউম / সিভি','দ্রুত লোডিং','স্ক্রল অ্যানিমেশন'),
 'active',0,3),
(4,4,'Landing Page','ল্যান্ডিং পেজ','landing-page',
 'High-converting single pages built to turn visitors into leads and sales.',
 'ভিজিটরকে লিড ও বিক্রিতে রূপান্তর করার জন্য নির্মিত উচ্চ-কনভার্সন সিঙ্গেল পেজ।',
 'Launch fast with a focused, conversion-optimized landing page. Custom hero, persuasive copy section, lead-capture form and social proof — ready to run ads in days, not months.',
 'দ্রুত লঞ্চ করুন একটি ফোকাসড, কনভার্সন-অপ্টিমাইজড ল্যান্ডিং পেজ দিয়ে। কাস্টম হিরো, প্ররোচনামূলক কপি সেকশন, লিড ক্যাপচার ফর্ম ও সোশ্যাল প্রুফ — মাস নয়, দিনের মধ্যে অ্যাড চালানোর জন্য রেডি।',
 5000.00,'Fixed',
 JSON_ARRAY('Conversion-focused copy','Hero + CTA sections','Lead capture form','Social proof','Analytics ready'),
 JSON_ARRAY('কনভার্সন-ফোকাসড কপি','হিরো + সিটিএ সেকশন','লিড ক্যাপচার ফর্ম','সোশ্যাল প্রুফ','অ্যানালিটিক্স রেডি'),
 'active',0,4),
(5,5,'Custom Application','কাস্টম অ্যাপ্লিকেশন','custom-application',
 'Bespoke web applications tailored to your exact business workflow.',
 'আপনার ব্যবসার কাজের ধারা অনুযায়ী তৈরি বিশেষায়িত ওয়েব অ্যাপ্লিকেশন।',
 'Standard templates do not win markets. We build purpose-built tools — CRM-ish dashboards, ticket systems, school/coaching portals, HR software — matched to how your team actually works, with bilingual (EN/বাংলা) interfaces when you need them.',
 'স্ট্যান্ডার্ড টেমপ্লেট বাজার জেতে না। আমরা বানাই আপনার টিমের কাজের ধরনের সঙ্গে মানানসই টুল — সিআরএম-ধাঁচের ড্যাশবোর্ড, টিকেট সিস্টেম, স্কুল/কোচিং পোর্টাল, এইচআর সফটওয়্যার। প্রয়োজন হলে থাকবে দ্বিভাষিক (EN/বাংলা) ইন্টারফেস।',
 15000.00,'Custom Quote',
 JSON_ARRAY('Bespoke workflow design','Bilingual interface','CRM / ERP concepts','Third-party integrations','Priority support'),
 JSON_ARRAY('অনুযায়ী ওয়ার্কফ্লো ডিজাইন','দ্বিভাষিক ইন্টারফেস','সিআরএম / ইআরপি কনসেপ্ট','থার্ড-পার্টি ইন্টিগ্রেশন','প্রায়োরিটি সাপোর্ট'),
 'active',1,5),
(6,5,'Corporate Website','কর্পোরেট ওয়েবসাইট','corporate-website',
 'Polished multi-page company sites with news, services and contact sections.',
 'নিউজ, সার্ভিস ও যোগাযোগ সেকশনসহ পেশাদার মাল্টি-পেজ কোম্পানি ওয়েবসাইট।',
 'A credible online presence for companies that need to impress partners and investors: services showcase, team page, latest news, and strong inquiry capture — all bilingual and blazing fast.',
 'পার্টনার ও ইনভেস্টরদের প্রভাবিত করতে প্রয়োজনীয় বিশ্বাসযোগ্য অনলাইন উপস্থিতি: সার্ভিস শোকেস, টিম পেজ, সর্বশেষ নিউজ ও ইনকোয়ারি ক্যাপচার — সব দ্বিভাষিক ও অত্যন্ত দ্রুত।',
 20000.00,'Starts from',
 JSON_ARRAY('Multi-page structure','Services showcase','News / blog','Inquiry forms','Bilingual (EN + বাংলা)'),
 JSON_ARRAY('মাল্টি-পেজ স্ট্রাকচার','সার্ভিস শোকেস','নিউজ / ব্লগ','ইনকোয়ারি ফর্ম','দ্বিভাষিক (EN + বাংলা)'),
 'inactive',0,6),
(7,4,'One-Page Promo Site','ওয়ান-পেজ প্রোমো সাইট','one-page-promo-site',
 'A single scrolling showcase for flash sales, product launches or events.',
 'ফ্ল্যাশ সেল, প্রোডাক্ট লঞ্চ বা ইভেন্টের জন্য একটি স্ক্রলিং শোকেস।',
 'Archived sample service — this row demonstrates that archived services stay in the database (existing orders remain intact) while hidden from the public site.',
 'আর্কাইভড স্যাম্পল সার্ভিস — এই রোটি দেখায় আর্কাইভকৃত সার্ভিস ডেটাবেসে থেকে যায় (আগের অর্ডার অক্ষত থাকে) কিন্তু পাবলিক সাইট থেকে লুকানো থাকে।',
 3000.00,'Fixed',
 JSON_ARRAY('Flash-sale hero','Product highlights','Countdown timer','WhatsApp CTA'),
 JSON_ARRAY('ফ্ল্যাশ-সেল হিরো','প্রোডাক্ট হাইলাইট','কাউন্টডাউন টাইমার','হোয়াটসঅ্যাপ সিটিএ'),
 'archived',0,7);

-- ---- orders ------------------------------------------------------------
INSERT INTO `orders` (`id`,`user_id`,`service_id`,`name`,`email`,`phone`,`project_type`,`budget`,`details`,`status`) VALUES
(1,2,1,'Rahim Karim','customer@demo.com','01711223344','E-Commerce Store',30000.00,'Boutique clothing store with bKash & Nagad. Need ~50 products and an admin panel.','in_progress'),
(2,3,NULL,'Tasnim Binte Ali','verified@demo.com','01822334455','Landing Page (Custom)',8000.00,'Landing page for my UI/UX portfolio services, with a WhatsApp CTA.','delivered'),
(3,NULL,2,'Shafiq Rahman','shafiq@example.com','01633445566','SaaS Platform',65000.00,'A coaching institute management SaaS with billing and parent portal.','pending'),
(4,4,5,'Nafis Hasan','full@demo.com','01933445566','Custom Application',40000.00,'Inventory + billing tool for a hardware shop, bilingual UI.','pending');

-- ---- chat: one demo conversation + one open bot session ------------------
INSERT INTO `chat_sessions` (`id`,`session_token`,`user_id`,`guest_name`,`bot_mode`,`admin_taken`,`status`) VALUES
(1,'demo-chat-0001',4,'Nafis Hasan',1,0,'open');
INSERT INTO `chat_messages` (`chat_id`,`sender`,`message`) VALUES
(1,'guest','Hi! I want to build an e-commerce store for my shop.'),
(1,'bot','Welcome to Aurora Cyber! 👋 We build fast, modern websites in Bangladesh. How can I help you today?'),
(1,'guest','How much does an e-commerce site cost?'),
(1,'bot','Our E-Commerce Store package starts from ৳ 25,000 — that includes product catalog, cart, checkout and bKash/Nagad integration. Want me to connect you with our team?');

-- ---- translations (JSON overrides merged into the JS dictionary) ---------
INSERT INTO `translations` (`dict_key`,`en`,`bn`) VALUES
('nav_order_project','Start a project','প্রজেক্ট শুরু করুন'),
('hero_cta_primary','Build my website','আমার ওয়েবসাইট বানান'),
('chat_title','Aurora Assistant','অরোরা সহকারী');