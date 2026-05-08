---
title: Recovering from a bad UPDATE statement
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recovering-from-a-bad-update-statement/
  post_id: 3834
source_author:
  name: Michael Coburn
  slug: michael-coburn
  url: https://www.percona.com/blog/author/michael-coburn/
  website: ''
published_at: '2012-10-19T20:31:07'
published_at_gmt: '2012-10-19T20:31:07'
modified_at: '2026-05-05T16:50:06'
modified_at_gmt: '2026-05-05T16:50:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recovering from a bad UPDATE statement

Source: [Percona Blog](https://www.percona.com/blog/recovering-from-a-bad-update-statement/)

Auteur source: [Michael Coburn](https://www.percona.com/blog/author/michael-coburn/)

Publication: 2012-10-19T20:31:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Did you just run an UPDATE against your 10 million row users table without a WHERE clause? Did you know that in MySQL 5.5 that sometimes you can recover from a bad UPDATE statement? This is possible if you are running in binlog_format=ROW ! Imagine this scenario: CREATE TABLE `t1` ( `c1` int(11) NOT NULL AUTO_INCREMENT, `c2` varchar(10) NOT NULL, PRIMARY KEY (`c1`) ) ENGINE=InnoDB; INSERT INTO `t1` (`c2`) VALUES ('michael'), ('peter'), ('aamina'); 1 2 3 4 5 6 CREATE TABLE ` t1 ` ( ` c1 ` int ( 11 ) NOT NULL AUTO_INCREMENT , ` c2 ` varchar ( 10 ) NOT NULL , PRIMARY KEY ( ` c1 ` ) ) ENGINE = InnoDB ; INSERT INTO ` t1 ` ( ` c2 ` ) VALUES ( 'michael' ) , ( 'peter' ) , ( 'aamina' ) ; We run an accidental UPDATE statement that … Continued

## Auteur source

Michael Coburn works at Percona on the Professional Services team in the role of Principal Architect. Michael joined Percona in 2012 as a Consultant after having worked as a DBA with stock photography websites and email service provider platforms. With a foundation in Systems Administration, Michael previously served as Product Manager responsible for Percona Monitoring and Management (PMM).
