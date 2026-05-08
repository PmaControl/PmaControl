---
title: Storing UUID Values in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/store-uuid-optimized-way/
  post_id: 8841
source_author:
  name: Karthik Appigatla
  slug: karthik-appigatla
  url: https://www.percona.com/blog/author/karthik-appigatla/
  website: http://www.percona.com/blog/
published_at: '2014-12-19T14:00:18'
published_at_gmt: '2014-12-19T14:00:18'
modified_at: '2026-05-05T16:56:38'
modified_at_gmt: '2026-05-05T16:56:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- auto_increment
- BTREE index
- Karthik Appigatla
- MySQL
- Peter Zaitsev
- Primary
- Universal Unique Identifier
- UUID
tag_slugs:
- auto_increment
- btree-index
- karthik-appigatla
- mysql
- peter-zaitsev
- primary
- universal-unique-identifier
- uuid
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/4-4.png
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Storing UUID Values in MySQL

Source: [Percona Blog](https://www.percona.com/blog/store-uuid-optimized-way/)

Auteur source: [Karthik Appigatla](https://www.percona.com/blog/author/karthik-appigatla/)

Publication: 2014-12-19T14:00:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Please note, a more up-to-date follow-up post is here: Storing UUID and Generated Columns A few years ago Peter Zaitsev, in a post titled ““There is timestamp based part in UUID which has similar properties to auto_increment and which could be used to have values generated at the same point in time physically local in … Continued

## Structure detectee

- H2: Problems with UUID
- H2: Structure of UUID
- H2: Benchmarking
- H2: Conclusions for storing UUID Values
- H3: References

## Images et graphiques reperes

- featured / image: [Storing UUID Values in MySQL](https://www.percona.com/wp-content/uploads/2026/03/4-4.png)
- content / image: [Need help with your database environment? Talk to a Percona expert.](https://www.percona.com/wp-content/uploads/2026/03/024fb701-a119-410b-9f62-7e0b0e387a9f.png)
- content / image: [Storing UUID Values](https://www.percona.com/wp-content/uploads/2026/03/11-1024x512.png)
- content / image: [Index Size](https://www.percona.com/wp-content/uploads/2026/03/21-1.png)
- content / image: [Total Size](https://www.percona.com/wp-content/uploads/2026/03/31-1.png)
- content / image: [Time Taken](https://www.percona.com/wp-content/uploads/2026/03/41-1024x597.png)

## Auteur source

Karthik joined Percona in September 2014 as a Remote DBA. His duties include handling remote DBA operations and collaborating closely with the team to evolve the RDBA offering. Karthik has 6+ years of experience in database administration. He began his career with Yahoo! as a Service Engineer (DevOps) and slowly converted into a MySQL DBA. After working for close to 5 years at Yahoo!, he moved to Pythian as a Database Consultant and got promoted to Lead Database Consultant. He holds certifications from RedHat (RHCSA & RHCE), Oracle (MySQL-5.5 OCP) and MongoDB (10GEN Certified DBA & Developer) Originally from the holy city Tirupati (India), recently relocated to Hyderabad(India) after marriage. Karthik plays cricket, chess and caroms during free time.
