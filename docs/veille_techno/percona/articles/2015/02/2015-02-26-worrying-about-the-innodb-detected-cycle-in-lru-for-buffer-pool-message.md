---
title: 'Worrying about the ‘InnoDB: detected cycle in LRU for buffer pool (…)’ message?'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/worrying-about-the-innodb-detected-cycle-in-lru-for-buffer-pool-message/
  post_id: 9070
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2015-02-26T08:00:50'
published_at_gmt: '2015-02-26T08:00:50'
modified_at: '2026-04-28T22:17:05'
modified_at_gmt: '2026-04-28T22:17:05'
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
- Ernie Souhrada
- Fernando Laudares
- George Lorch
- InnoDB
- InnoDB buffer pool
- Least Recently Used
- LRU
- MySQL
- Primary
tag_slugs:
- ernie-souhrada
- fernando-laudares
- george-lorch
- innodb
- innodb-buffer-pool
- least-recently-used
- lru
- mysql
- primary
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Worrying about the ‘InnoDB: detected cycle in LRU for buffer pool (…)’ message?

Source: [Percona Blog](https://www.percona.com/blog/worrying-about-the-innodb-detected-cycle-in-lru-for-buffer-pool-message/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2015-02-26T08:00:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you use Percona Server 5.5 and you have configured it to use multiple buffer pool instances than sooner or later you’ll see the following lines on the server’s error log and chances are you’ll be worried about them: Shell InnoDB: detected cycle in LRU for buffer pool 5, skipping to next buffer pool. InnoDB: detected cycle in LRU for buffer pool 3, skipping to next buffer pool. InnoDB: detected cycle in LRU for buffer pool 7, skipping to next buffer pool. 1 2 3 InnoDB : detected cycle in LRU for buffer pool 5 , skipping to next buffer pool . InnoDB : detected cycle in LRU for buffer pool 3 , skipping to next buffer pool . InnoDB : detected cycle in LRU for buffer pool 7 , skipping to next buffer pool . Worry not as this is mostly harmless. It’s becoming a February tradition for me (Fernando) … Continued

## Structure detectee

- H2: InnoDB internals: what is “LRU” ?
- H2: Dumping and reloading the buffer pool
- H2: “Detected cycle in LRU”
- H2: How harmless are those messages ?

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
