---
title: InnoDB locks and deadlocks with or without index for different isolation level
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-locks-deadlocks-without-index-different-isolation-level/
  post_id: 9165
source_author:
  name: Nilnandan Joshi
  slug: nilnandan-joshi-2
  url: https://www.percona.com/blog/author/nilnandan-joshi-2/
  website: ''
published_at: '2015-04-09T18:27:07'
published_at_gmt: '2015-04-09T18:27:07'
modified_at: '2026-04-28T22:18:09'
modified_at_gmt: '2026-04-28T22:18:09'
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
- Percona Live
category_slugs:
- mysql
- percona-live
tags:
- InnoDB locks
- MySQL
- Nilnandan Joshi
- Peiran Song
- percona live
- Primary
tag_slugs:
- innodb-locks
- mysql
- nilnandan-joshi
- peiran-song
- percona-live
- primary
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB locks and deadlocks with or without index for different isolation level

Source: [Percona Blog](https://www.percona.com/blog/innodb-locks-deadlocks-without-index-different-isolation-level/)

Auteur source: [Nilnandan Joshi](https://www.percona.com/blog/author/nilnandan-joshi-2/)

Publication: 2015-04-09T18:27:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I was working on one of the issue related to locks and deadlocks with InnoDB tables and I found very interesting details about how InnoDB locks and deadlocks works with or without index for different Isolation levels. Here, I would like to describe a small test case about how SELECT ..FOR UPDATE (with and … Continued

## Auteur source

Nilnandan officially started with Percona as a Support Engineer. Before joining Percona, he has worked as a MySQL Database administrator with different types of service based companies managing high-traffic websites and web applications. Nilnandan has extensive experience in database design and development, database management, client management, security/documentations/training, implementing DRM solutions, automating backups and high availability. Nilnandan is based at Pune (India). In his spare time, he likes to listen Indian classical/semi-classical music, watching tv, playing cricket/badminton and hang out with his family.
