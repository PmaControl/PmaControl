---
title: How MySQL Uses character_set Configurations
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-mysql-uses-character_set-configurations/
  post_id: 26418
source_author:
  name: Edwin Wang
  slug: edwin-wang
  url: https://www.percona.com/blog/author/edwin-wang/
  website: ''
published_at: '2022-12-27T13:17:16'
published_at_gmt: '2022-12-27T13:17:16'
modified_at: '2026-03-26T20:30:23'
modified_at_gmt: '2026-03-26T20:30:23'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-MySQL-Uses-character_set.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How MySQL Uses character_set Configurations

Source: [Percona Blog](https://www.percona.com/blog/how-mysql-uses-character_set-configurations/)

Auteur source: [Edwin Wang](https://www.percona.com/blog/author/edwin-wang/)

Publication: 2022-12-27T13:17:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are eight configuration options related to the character_set in MySQL, as shown below. Without reading the MySQL Character Set documentation carefully, it could be hard to know what these configuration options are used for. In addition, for some of the options, unless there is further testing, it could be hard to know how MySQL … Continued

## Structure detectee

- H2: Grouping the options
- H3: G1. Miscellaneous
- H3: G2. Define the character set of data (column in a table)
- H3: G3. Transfer/interpret during the processing of the character_set
- H2: Illustration via examples
- H3: Example one
- H3: Example two
- H3: Example 2.1 convert from smaller character_set to larger character_set
- H3: Example 2.2 convert from larger character_set to smaller character_set
- H3: Example three
- H3: Example 3.1 converts from a smaller character_set to a larger character_set.
- H3: Example 3.2 converts from a larger character_set to a smaller character_set.
- H3: Example four
- H3: Example five
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [How MySQL Uses character_set Configurations](https://www.percona.com/wp-content/uploads/2026/03/How-MySQL-Uses-character_set.png)

## Auteur source

A father with 1 wife, 2 kids, and 2 dogs. A DBA with 20 years of experience in RDBMS i.e. MySQL, Oracle Etc. Currently working at Percona as Senior Mysql Database Administrator working on different environments and scenarios, including database installation/configuration/maintenance, trouble-shooting, design, performance tuning, DB High Availability architecture, and other infrastructure-related issues, AWS cloud, ansible, GCP, etc.
