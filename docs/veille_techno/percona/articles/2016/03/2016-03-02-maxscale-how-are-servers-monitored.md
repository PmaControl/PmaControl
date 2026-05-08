---
title: How MaxScale monitors servers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/maxscale-how-are-servers-monitored/
  post_id: 14679
source_author:
  name: Frederic Descamps
  slug: lefred
  url: https://www.percona.com/blog/author/lefred/
  website: http://www.lefred.be
published_at: '2016-03-02T15:56:20'
published_at_gmt: '2016-03-02T15:56:20'
modified_at: '2026-05-05T19:36:17'
modified_at_gmt: '2026-05-05T19:36:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MaxScale
- MySQL
matched_filters:
- category:mysql:83
- search:maxscale
- tag:maxscale:1758
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MaxScale
- monitor
- Server
tag_slugs:
- maxscale
- monitor
- server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/maxscale-monitors-servers.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How MaxScale monitors servers

Source: [Percona Blog](https://www.percona.com/blog/maxscale-how-are-servers-monitored/)

Auteur source: [Frederic Descamps](https://www.percona.com/blog/author/lefred/)

Publication: 2016-03-02T15:56:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, we’ll address how MaxScale monitors servers. We saw in the We saw in the previous post how we could deal with high availability (HA) and read-write split using MaxScale. If you remember from the previous post, we used this section to monitor replication: [Replication Monitor] type=monitor module=mysqlmon servers=percona1, percona2, percona3 user=maxscale passwd=264D375EC77998F13F4D0EC739AABAD4 monitor_interval=1000 script=/usr/local/bin/failover.sh events=master_down 1 2 3 4 5 6 7 8 9 [ Replication Monitor ] type = monitor module = mysqlmon servers = percona1 , percona2 , percona3 user = maxscale passwd = 264D375EC77998F13F4D0EC739AABAD4 monitor_interval = 1000 script = / usr / local / bin / failover . sh events = master_down But what are we monitoring? We are monitoring … Continued

## Images et graphiques reperes

- featured / image: [How MaxScale monitors servers](https://www.percona.com/wp-content/uploads/2026/03/maxscale-monitors-servers.jpg)
- content / image: [maxscale monitors servers](https://www.percona.com/wp-content/uploads/2026/03/maxscale-monitors-servers-300x163.jpg)

## Auteur source

Frédéric joined Percona in June 2011, he is an experienced Open Source consultant with expertise in infrastructure projects as well in development tracks and database administration. Frédéric is a believer of devops culture.
