---
title: When is MIN(DATE) != MIN(DATE) ?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/odd_date_values_and_input_range_checking/
  post_id: 3749
source_author:
  name: Ernie Souhrada
  slug: percona_ews
  url: https://www.percona.com/blog/author/percona_ews/
  website: http://www.percona.com
published_at: '2012-09-04T12:47:38'
published_at_gmt: '2012-09-04T12:47:38'
modified_at: '2026-04-28T21:39:19'
modified_at_gmt: '2026-04-28T21:39:19'
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

# When is MIN(DATE) != MIN(DATE) ?

Source: [Percona Blog](https://www.percona.com/blog/odd_date_values_and_input_range_checking/)

Auteur source: [Ernie Souhrada](https://www.percona.com/blog/author/percona_ews/)

Publication: 2012-09-04T12:47:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Inspiration for this post is courtesy of a friend and former colleague of mine, Greg Youngblood, who pinged me last week with an interesting MySQL puzzle. He was running Percona Server 5.5.21 with a table structure that looks something like this: CREATE TABLE foo ( id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, uid INT UNSIGNED NOT NULL, update_time DATETIME NOT NULL, .... INDEX `uid` (uid, update_time), INDEX `bar` (some_other_columns) .... ) ENGINE=InnoDB; 1 2 3 4 5 6 7 8 9 CREATE TABLE foo ( id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , uid INT UNSIGNED NOT NULL , update_time DATETIME NOT NULL , . . . . INDEX ` uid ` ( uid , update_time ) , INDEX ` bar ` ( some_other_columns ) . . . . ) ENGINE = InnoDB ; When he ran this query: SELECT MIN(update_time) FROM foo WHERE update_time IS NOT NULL AND update_time <> '0000-00-00 00:00:00'; 1 SELECT MIN ( update_time ) FROM foo...

## Auteur source

Ernie joined Percona in April 2012 as a Senior Consultant. In his previous lives, he has been everything from a Perl/Java developer to a Linux sysadmin, a MySQL DBA to a Cisco network engineer, and a security auditor to an IT engineering manager, many of these things all at the same time. When not working on MySQL, he might be found on the ski slope, at a psytrance festival, or at the nearest sushi bar.
