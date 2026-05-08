---
title: Auditing MySQL with McAfee and MongoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/auditing-mysql-with-mcafee-and-mongodb/
  post_id: 9309
source_author:
  name: Matthew Boehm
  slug: matthew-boehm
  url: https://www.percona.com/blog/author/matthew-boehm/
  website: https://www.percona.com/training
published_at: '2015-06-09T13:00:54'
published_at_gmt: '2015-06-09T13:00:54'
modified_at: '2026-03-26T20:23:03'
modified_at_gmt: '2026-03-26T20:23:03'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MongoDB
- MySQL
- Percona Software
category_slugs:
- mongodb
- mysql
- percona-software
tags:
- AWS
- Matthew Boehm
- McAfee
- MongoDB
- MySQL
- Percona Server for MySQL
tag_slugs:
- aws
- matthew-boehm
- mcafee
- mongodb
- mysql
- percona-server
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Auditing MySQL with McAfee and MongoDB

Source: [Percona Blog](https://www.percona.com/blog/auditing-mysql-with-mcafee-and-mongodb/)

Auteur source: [Matthew Boehm](https://www.percona.com/blog/author/matthew-boehm/)

Publication: 2015-06-09T13:00:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Greetings everyone! Let’s discuss a 3rd Party auditing solution to MySQL and how we can leverage MongoDB® to make sense out of all of that data. The McAfee MySQL Audit plugin does a great job of capturing, at low level, activities within a MySQL server. It does this through some non-standard APIs which is why installing … Continued

## Structure detectee

- H2: Install McAfee Audit Plugin
- H2: Setting Up MongoDB
- H2: Making Data Make Sense
- H2: !! MongoDB BUG !!
- H3: Basic Command Counters
- H3: User Counts
- H3: Specific User Activities
- H3: Activities By User
- H2: Table Activity
- H2: Conclusion

## Auteur source

Matthew joined Percona in the fall of 2012 as a MySQL Consultant; now Principal Architect / Senior Instructor. His areas of knowledge include the traditional LAMP stack, MySQL high availability, massive sharding topologies, and PHP/GoLang/C/C++ MySQL development. Previously, Matthew was a DBA for the 5th largest world-wide MySQL installation at eBay/PayPal. During his off-hours, Matthew is a nationally ranked, competitive West Coast Swing dancer and travels to competitions around the US. He enjoys working out, camping, biking, and shooting Junior-Olympic Recurve Archery with his oldest son.
