---
title: Setup Compatible OpenLDAP Server for MongoDB and MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setup-compatible-openldap-server-for-mongodb-and-mysql/
  post_id: 19636
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2018-11-27T13:48:38'
published_at_gmt: '2018-11-27T13:48:38'
modified_at: '2026-03-26T20:17:23'
modified_at_gmt: '2026-03-26T20:17:23'
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
category_slugs:
- mongodb
- mysql
tags:
- authentication
- LDAP
- LDAP. authentication
tag_slugs:
- authentication
- ldap
- ldap-authentication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/LDAP-authentication-for-MySQL-MongoDB.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setup Compatible OpenLDAP Server for MongoDB and MySQL

Source: [Percona Blog](https://www.percona.com/blog/setup-compatible-openldap-server-for-mongodb-and-mysql/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2018-11-27T13:48:38

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

By the end of this article, you should be able to have a Percona Server for MongoDB and Percona Server for MySQL instance able to authenticate on an OpenLDAP backend. While this is mostly aimed at testing scenarios, it can be easily extended for production by following the OpenLDAP production best practices i.e. attending to … Continued

## Structure detectee

- H2: PAM Configuration for MySQL
- H3: /etc/nslcd.conf
- H3: /etc/nsswitch.conf
- H2: SASL for MongoDB
- H3: /etc/mongod.conf
- H3: /etc/saslauthd.conf

## Images et graphiques reperes

- featured / image: [Setup Compatible OpenLDAP Server for MongoDB and MySQL](https://www.percona.com/wp-content/uploads/2026/03/LDAP-authentication-for-MySQL-MongoDB.jpg)

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
