# PowerUp B2C 边界测试脚本清单

## A. 价格与多币种
1. 0元商品
- 创建价格为 0 的 simple product
- 访问产品页与购物车
- 预期：可展示，不报错；结账价格正确为 0

2. 缺货商品
- 库存=0 且 outofstock
- 预期：不可下单；库存锁不会放行

3. 下架商品
- 状态 draft/private
- 预期：前台不可购买，ERP stock-price 同步时返回 not_found 或跳过

4. 多币种切换一致性
- 访问 `/?powerup_currency=EUR`
- 检查列表页、购物车、结账页
- 预期：列表/详情按汇率换算；结账统一 checkout_currency

## B. 订单与支付
1. 未登录下单
- 游客下单（PayPal/Stripe 测试模式）
- 预期：订单可创建，ERP order_created 触发一次

2. 状态流转
- pending -> processing -> completed
- 预期：ERP status push 按状态变化触发，且同一 provider/event 不重复推

## C. ERP 入站 API
通用请求头（二选一校验）：
- `X-PowerUp-Token: your-token`
- 或 `X-PowerUp-Signature + X-PowerUp-Timestamp + X-PowerUp-Nonce`

### 1) 商品同步 ERP -> Woo
```bash
curl -X POST "http://powerup.local/wp-json/powerup-b2c/v1/erp/dianxiaomi/products" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Token: YOUR_TOKEN" \
  -d '{
    "items": [
      {"sku":"SKU-EDGE-001","name":"Edge Product","price":19.99,"stock":12}
    ]
  }'
```
预期：返回 ok=true，商品创建或更新成功。

### 2) 库存价格同步 ERP -> Woo
```bash
curl -X POST "http://powerup.local/wp-json/powerup-b2c/v1/erp/dianxiaomi/stock-price" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Token: YOUR_TOKEN" \
  -d '{
    "items": [
      {"sku":"SKU-EDGE-001","price":25.5,"stock":3}
    ]
  }'
```
预期：库存和价格更新成功。

### 3) 物流回传 ERP -> Woo（幂等）
```bash
curl -X POST "http://powerup.local/wp-json/powerup-b2c/v1/erp/dianxiaomi/shipments" \
  -H "Content-Type: application/json" \
  -H "X-PowerUp-Token: YOUR_TOKEN" \
  -d '{
    "order_id": 11,
    "event_id": "ship-evt-10001",
    "carrier": "YunExpress",
    "tracking_number": "YT1234567890123"
  }'
```
重复发送同一 event_id：
- 预期：返回 duplicate=true，不重复写状态。

## D. 安全测试
1. 去掉 nonce 提交 contact/subscribe
- 预期：返回 invalid，数据不入库不发邮件。

2. 回调重放
- 复用同一个 `X-PowerUp-Nonce`
- 预期：401 replay_request。

3. 过期请求
- `X-PowerUp-Timestamp` 使用 10 分钟前时间
- 预期：401 expired_request。

## E. 重试与日志
1. 故意填错 ERP endpoint
- 下单后查看 Woo 订单备注
- 预期：记录推送失败，进入重试队列。

2. 修复 endpoint 后等待 Cron
- 预期：重试成功，Woo 日志 source=powerup-b2c-erp 可追踪。
