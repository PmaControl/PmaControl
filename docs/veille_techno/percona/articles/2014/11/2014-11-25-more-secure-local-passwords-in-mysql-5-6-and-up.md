---
title: (More) Secure local passwords in MySQL 5.6 and up
source:
  name: Percona Blog
  url: https://www.percona.com/blog/more-secure-local-passwords-in-mysql-5-6-and-up/
  post_id: 8788
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2014-11-25T08:00:13'
published_at_gmt: '2014-11-25T08:00:13'
modified_at: '2026-05-04T20:57:45'
modified_at_gmt: '2026-05-04T20:57:45'
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
- Security
category_slugs:
- mysql
- security
tags:
- Jay Janssen
- my.cnf
- MySQL 5.6
- Primary
- Secure local passwords
tag_slugs:
- jay-janssen
- my-cnf
- mysql-5-6
- primary
- secure-local-passwords
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# (More) Secure local passwords in MySQL 5.6 and up

Source: [Percona Blog](https://www.percona.com/blog/more-secure-local-passwords-in-mysql-5-6-and-up/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2014-11-25T08:00:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I log into a lot of different servers running MySQL and one of the first things I do is create a file in my home directory called ‘.my.cnf’ with my credentials to that local mysql instance: [client] user=root password=secret 1 2 3 [ client ] user = root password = secret This means I don’t have to type my password in every time, nor am I tempted to include … Continued

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
