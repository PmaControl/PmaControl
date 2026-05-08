---
title: FusionIO – time for benchmarks
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fusionio-time-for-benchmarks/
  post_id: 2159
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2009-12-08T22:28:18'
published_at_gmt: '2009-12-08T22:28:18'
modified_at: '2026-03-23T21:36:59'
modified_at_gmt: '2026-03-23T21:36:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
category_slugs:
- benchmarks
- mysql
tags: []
tag_slugs: []
featured_image_url: http://chart.apis.google.com/chart?chtt=16KB random read, throughput&chs=600x300&cht=bvg&chxt=x,y,x,y&chdl=ioDrive|RAID10&chd=t:140.59,446.37,530.00,530.41,696.18,709.34,711.69,712.65|3.82,12,21.97,32.16,42.60,51.18,58.90,58.84&chbh=35&chco=FF0000,00FF00&chds=0,750&chxr=1,0,750&chxl=0:|1|4|8|16|32|64|128|512||2:|Threads||3:|MB/s|&chg=0,10&chm=N,000000,0,-1,11|N,000000,0,-1,11&chxp=2,50|3,50&chbh=a
image_count: 5
graph_or_chart_count: 5
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# FusionIO – time for benchmarks

Source: [Percona Blog](https://www.percona.com/blog/fusionio-time-for-benchmarks/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2009-12-08T22:28:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I posted about FusionIO couple times RAID vs SSD vs FusionIO and Testing FusionIO: strict_sync is too strictâ€¦. The problem was that FusionIO did not provide durability or results were too bad in strict mode, so I lost interest FusionIO for couple month. But I should express respect to FusionIO team, they did not ignore … Continued

## Images et graphiques reperes

- content / graph_or_chart: [chart](http://chart.apis.google.com/chart?chtt=16KB random read, throughput&chs=600x300&cht=bvg&chxt=x,y,x,y&chdl=ioDrive|RAID10&chd=t:140.59,446.37,530.00,530.41,696.18,709.34,711.69,712.65|3.82,12,21.97,32.16,42.60,51.18,58.90,58.84&chbh=35&chco=FF0000,00FF00&chds=0,750&chxr=1,0,750&chxl=0:|1|4|8|16|32|64|128|512||2:|Threads||3:|MB/s|&chg=0,10&chm=N,000000,0,-1,11|N,000000,0,-1,11&chxp=2,50|3,50&chbh=a)
- content / graph_or_chart: [chart](http://chart.apis.google.com/chart?chtt=16KB random write, throughput&chs=600x300&cht=bvg&chxt=x,y,x,y&chdl=ioDrive|RAID10&chd=t:131.68,316.15,162.96,203,204.09,181.57,187.38,185.83|17.48,18.934,19.366,19.24,19.39,19.44,19.39,19.66&chbh=35&chco=FF0000,00FF00&chds=0,330&chxr=1,0,330&chxl=0:|1|4|8|16|32|64|128|512||2:|Threads||3:|MB/s|&chg=0,10&chm=N,000000,0,-1,11&chxp=2,50|3,50&chbh=a)
- content / graph_or_chart: [chart](http://chart.apis.google.com/chart?chtt=16KB sequential read, throughput&chs=600x300&cht=bvg&chxt=x,y,x,y&chdl=ioDrive&chd=t:226.64,544.12,706.04,728.45,731.00,729.61,728.38,733.21&chbh=35&chco=FF0000,00FF00&chds=0,770&chxr=1,0,770&chxl=0:|1|4|8|16|32|64|128|512||2:|Threads||3:|MB/s|&chg=0,10&chm=N,000000,0,-1,11&chxp=2,50|3,50)
- content / graph_or_chart: [chart](http://chart.apis.google.com/chart?chtt=16KB sequential write, throughput&chs=600x300&cht=bvg&chxt=x,y,x,y&chdl=ioDrive&chd=t:129.12,99.434,97.361,84.424,75.217,75.735,78.347,72.744&chbh=35&chco=FF0000,00FF00&chds=0,170&chxr=1,0,170&chxl=0:|1|4|8|16|32|64|128|512||2:|Threads||3:|MB/s|&chg=0,10&chm=N,000000,0,-1,11&chxp=2,50|3,50)
- content / graph_or_chart: [chart](http://chart.apis.google.com/chart?chtt=16KB random write, throughput, 8 threads&chs=600x300&cht=bvg&chxt=x,y,x,y&chdl=ioDrive&chd=t:662.65,607.49,598.14,587.03,579.46,566.54,439.34,201.39,144.98&chbh=35&chco=FF0000,00FF00&chds=0,700&chxr=1,0,700&chxl=0:|1|2|4|8|16|32|64|96|112||2:|FileSize, GB||3:|MB/s|&chg=0,10&chm=N,000000,0,-1,11&chxp=2,50|3,50)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
