# Slave

- Type: class
- Namespace: `App\Controller`
- Source: `App/Controller/Slave.php`

- `index()`: Render slave state through `index`.
- `generateGraph($slaves)`: Handle slave state through `generateGraph`.
- `show($param)`: Handle slave state through `show`.
- `box()`: Handle slave state through `box`.
- `generateGraphSlave($slaves)`: Handle slave state through `generateGraphSlave`.
- `startSlave($param)`: Handle slave state through `startSlave`.
- `stopSlave($param)`: Handle slave state through `stopSlave`.
- `setSlave($param)`: Handle slave state through `setSlave`.
- `getInfoServer($id_mysql_server)`: Retrieve slave state through `getInfoServer`.
- `runInBackground($command, $log, $priority)`: Handle slave state through `runInBackground`.
- `isProcessRunning($PID)`: Handle slave state through `isProcessRunning`.
- `getMasterInfo($param)`: Retrieve slave state through `getMasterInfo`.
- `switchOver($param)`: Handle slave state through `switchOver`.
- `activateGtid($param)`: Handle slave state through `activateGtid`.
- `deactivateGtid($param)`: Handle slave state through `deactivateGtid`.
- `skipCounter($param)`: Handle slave state through `skipCounter`.
- `extractBinlogInfo($line)`: Handle slave state through `extractBinlogInfo`.
- `groupBinlogInfoByPosition($extractedData)`: Handle slave state through `groupBinlogInfoByPosition`.
- `processBinlogFile($param)`: Handle slave state through `processBinlogFile`.
- `generateCmd($param)`: Handle slave state through `generateCmd`.
- `execute($sql, $db, $DRY_RUN)`: Handle slave state through `execute`.
- `generateSecurePassword($length)`: Handle slave state through `generateSecurePassword`.
- `waitForSlavePosition($param)`: Handle slave state through `waitForSlavePosition`.
