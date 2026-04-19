# PowerUp B2C 一键自测 Runbook

## A. 准备参数
```bash
export BASE_URL="http://powerup.local"
export TOKEN="YOUR_WEBHOOK_TOKEN"
export PROVIDER="dianxiaomi"
export ORDER_ID="11"
```

## B. ERP 入站接口 Smoke Test
### 1) 商品同步
```bash
curl -s -X POST "$BASE_URL/wp-json/powerup-b2c/v1/erp/$PROVIDER/products" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Token: $TOKEN" \
  -d '{"items":[{"sku":"SKU-SMOKE-001","name":"Smoke Product","price":39.9,"stock":20}]}'
```

### 2) 库存价格同步
```bash
curl -s -X POST "$BASE_URL/wp-json/powerup-b2c/v1/erp/$PROVIDER/stock-price" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Token: $TOKEN" \
  -d '{"items":[{"sku":"SKU-SMOKE-001","price":41.5,"stock":18}]}'
```

### 3) 物流回传（含幂等验证）
```bash
curl -s -X POST "$BASE_URL/wp-json/powerup-b2c/v1/erp/$PROVIDER/shipments" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Token: $TOKEN" \
  -d '{"order_id":'$ORDER_ID',"event_id":"ship-smoke-1001","carrier":"YunExpress","tracking_number":"YT000111222333"}'
```

重复发送同一请求一次，确认返回 duplicate=true。

## C. 防重放测试
### 1) 过期时间戳（应返回 401）
```bash
OLD_TS=$(($(date +%s)-1200))
BODY='{"items":[{"sku":"SKU-OLD-1","name":"Old","price":1,"stock":1}]}'
curl -s -X POST "$BASE_URL/wp-json/powerup-b2c/v1/erp/$PROVIDER/products" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Timestamp: $OLD_TS" \
  -H "X-PowerUp-Nonce: nonce-old" \
  -H "X-PowerUp-Signature: invalid" \
  -d "$BODY"
```

## D. 业务页面快速检查
1. 打开 Shop、PDP、Cart、Checkout、My Account。
2. PDP 检查划线价与 Marketplace 按钮是否展示正常。
3. Contact/Subscribe 表单提交后是否出现 success 提示。

## E. 运维指标检查
1. 后台进入 `WooCommerce -> B2C Ops Dashboard`
2. 检查：
- ERP Retry Queue 应接近 0
- Last Exchange Sync (UTC) 不为空
- Recent ERP Push Notes 有最新记录

## F. 失败排查优先级
1. 先看订单备注（是否 ERP 推送失败）
2. 再看 Woo 日志 source=powerup-b2c-erp
3. 最后看服务器 PHP error log
