---
title: Update Percona Monitoring and Management Agent Settings for Already-Added Instances
source:
  name: Percona Blog
  url: https://www.percona.com/blog/update-percona-monitoring-and-management-agent-settings-for-already-added-instances/
  post_id: 27223
source_author:
  name: Taras Onishchuk
  slug: taras-onishchuk
  url: https://www.percona.com/blog/author/taras-onishchuk/
  website: ''
published_at: '2023-07-19T13:02:03'
published_at_gmt: '2023-07-19T13:02:03'
modified_at: '2026-03-26T20:29:26'
modified_at_gmt: '2026-03-26T20:29:26'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- MySQL
- mysql-and-variants
- Percona Monitoring and Management
tag_slugs:
- mysql
- mysql-and-variants
- percona-monitoring-and-management
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/alternative-approach-to-re-adding-a-MySQL-instance.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Update Percona Monitoring and Management Agent Settings for Already-Added Instances

Source: [Percona Blog](https://www.percona.com/blog/update-percona-monitoring-and-management-agent-settings-for-already-added-instances/)

Auteur source: [Taras Onishchuk](https://www.percona.com/blog/author/taras-onishchuk/)

Publication: 2023-07-19T13:02:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When adding a remote MySQL instance to Percona Monitoring and Management (PMM), there are a few options you can specify during the setup, but they are not editable once added. For example, a table statistics limit is introduced to avoid querying information_schema.tables that may impact DB performance, especially with a high number of DBs / … Continued

## Structure detectee

- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Update Percona Monitoring and Management Agent Settings for Already-Added Instances](https://www.percona.com/wp-content/uploads/2026/03/alternative-approach-to-re-adding-a-MySQL-instance.png)
- content / image: [table statistics limit](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2023-07-07-at-10.51.00.png)

## Auteur source

Taras has studied Mechanical Engineering and holds a Master's Degree in Aircraft Engines and Power Plants, but has always been passionate about computers and IT. As a junior student, he started working in the web hosting industry. Since 2012, Taras has been part of the DB Ops team in a tech company, managing software databases; and has also received a diploma in Software Engineering. Before joining Percona in early 2019, he worked for 5 years as a MySQL and Elasticsearch DBA in the geolocation/geofencing and fraud prevention industry.
