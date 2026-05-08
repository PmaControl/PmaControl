---
title: Uninitialized data in the TokuDB recovery log
source:
  name: Percona Blog
  url: https://www.percona.com/blog/uninitialized-data-in-the-tokudb-recovery-log/
  post_id: 9860
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2014-04-03T16:04:02'
published_at_gmt: '2014-04-03T16:04:02'
modified_at: '2026-05-04T21:00:51'
modified_at_gmt: '2026-05-04T21:00:51'
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
- MySQL
- TokuDB
- Valgrind
tag_slugs:
- mysql
- tokudb
- valgrind
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Uninitialized data in the TokuDB recovery log

Source: [Percona Blog](https://www.percona.com/blog/uninitialized-data-in-the-tokudb-recovery-log/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2014-04-03T16:04:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A TokuDB MySQL test run with valgrind reported an uninitialized data error when writing into the TokuDB recovery log. ==1032== Syscall param write(buf) points to uninitialised byte(s) ==1032== at 0x3EFA60E4ED: ??? (in /lib64/libpthread-2.12.so) ==1032== by 0xB894038: toku_os_full_write(int, void const*, unsigned long) (file.cc:249) ==1032== by 0xB83248A: write_outbuf_to_logfile(tokulogger*, __toku_lsn*) (logger.cc:513) ==1032== by 0xB83326C: toku_logger_maybe_fsync(tokulogger*, __toku_lsn, int, bool) (logger.cc:836) ==1032== by 0xB8327DE: toku_logger_fsync_if_lsn_not_fsynced(tokulogger*, __toku_lsn) (logger.cc:586) ==1032== by 0xB8493E6: toku_txn_maybe_fsync_log(tokulogger*, __toku_lsn, bool) (txn.cc:600) ==1032== by 0xB7B4EBB: toku_txn_commit(__toku_db_txn*, unsigned int, void (*)(__toku_txn_progress*, void*), void*, bool, bool) (ydb_txn.cc:198) ==1032== by 0xB7B55E9: locked_txn_comm...
