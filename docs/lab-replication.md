# Lab Replication Legacy Entrypoints

`App/Controller/Demo.php` is retained temporarily as legacy lab material for
replication fixture work. It is not an HTTP feature and is blocked by
`App\Library\Security\RouteExposurePolicy` as part of issue #517.

`App/Controller/MasterSlave.php` was a duplicate lab controller and has been
removed. The `masterslave` controller key remains blocked in
`RouteExposurePolicy` as a tombstone, so an accidental reintroduction does not
restore HTTP reachability.

The remaining `Demo` controller is deprecated. It still mixes useful lab ideas
with unsafe side effects such as replication grants, shell execution, direct
route parameters and demo cleanup mutations. Do not promote it as a CLI contract
without extracting and hardening the reusable logic first.

The follow-up extraction is tracked in issue #665:

- move pure planning helpers into `App\Library\Lab\*`;
- keep any executable lab flow behind an explicit CLI entrypoint;
- remove hard-coded credentials and route-parameter SQL concatenation;
- delete `App/Controller/Demo.php` after the safe lab path exists.
