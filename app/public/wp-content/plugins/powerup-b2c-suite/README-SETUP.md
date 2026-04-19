# PowerUp B2C Suite 部署说明

## 1. 插件与主题
- 插件目录：`wp-content/plugins/powerup-b2c-suite`
- 子主题目录：`wp-content/themes/powerup-industrial-child`

## 2. 激活顺序
1. 后台启用插件 `PowerUp B2C Suite`
2. 后台启用子主题 `PowerUp Industrial Child`
3. 进入 `WooCommerce -> PowerUp B2C Suite` 填写配置并保存
4. 进入 `设置 -> 固定链接` 点一次保存（刷新 My Account endpoint）

说明：ERP 配置与推送入口已统一收敛在插件内，不再使用主题内 ERP 配置逻辑。
说明：联系表单与订阅表单处理也已迁移到插件，主题仅保留展示模板。
说明：Marketplace 按钮与 PDP 划线价逻辑已迁移到插件，主题不再持有对应业务代码。

## 3. ERP 配置
在 `WooCommerce -> PowerUp B2C Suite` 中填：
- Endpoint
- API Key
- API Secret
- Shop ID
- Webhook Token（可选，统一校验）

支持 ERP：
- 店小秘（dianxiaomi）
- 芒果店长（mangguo）
- 万里牛（wanliniu）
- 领星（lingxing）
- 易仓（yicang）

## 4. Webhook 地址
基础前缀：
`/wp-json/powerup-b2c/v1`

- 商品同步（ERP -> Woo）
  - `POST /erp/{provider}/products`
- 库存价格同步（ERP -> Woo）
  - `POST /erp/{provider}/stock-price`
- 物流回传（ERP -> Woo）
  - `POST /erp/{provider}/shipments`

示例 provider：`dianxiaomi`, `mangguo`, `wanliniu`, `lingxing`, `yicang`

## 5. 签名与安全
请求头任选其一：
- `X-PowerUp-Signature`: `hash_hmac('sha256', raw_body, api_secret)`
- `X-PowerUp-Token`: 后台设置的 `Webhook Token`

推荐增强：
- `X-PowerUp-Timestamp`
- `X-PowerUp-Nonce`

插件已支持防重放校验（过期请求与重复 nonce 会拒绝）。

## 6. 订单推送（Woo -> ERP）
自动触发：
- 下单后：`woocommerce_checkout_order_processed`
- 状态变更：`woocommerce_order_status_changed`

幂等控制：
- 自动生成 `event_id`
- 自动发送 `X-Idempotency-Key`
- 同一订单同一事件不会重复推送

失败自动入队重试：
- 队列 option：`powerup_b2c_erp_retry_queue`
- Cron：`powerup_b2c_retry_failed_pushes`（每 5 分钟）

## 7. 物流字段
ERP 回传后写入订单 meta：
- `_powerup_tracking_number`
- `_powerup_tracking_carrier`

## 8. 支付网关
- PayPal：`woocommerce-paypal-payments`
- Stripe：`woocommerce-gateway-stripe`
- Cardinity：安装官方插件后在 WooCommerce Payments 里启用；插件内统一了 checkout 货币策略。

## 9. 运费模板
新增配送方式：
- `PowerUp Carrier Shipping`
- 可按物流商（4PX/燕文/云途）+ 首重/续重 + 偏远地区附加费配置

## 10. 汇率自动刷新
- 配置项：`Auto Refresh Rates`、`Exchange API URL`
- 定时任务：`powerup_b2c_refresh_exchange_rates`（daily）
- 建议保留手工汇率作为兜底

## 11. 联调仪表页
- 菜单：`WooCommerce -> B2C Ops Dashboard`
- 可查看：
  - ERP 重试队列数量
  - 最近汇率同步时间（UTC）
  - 最近 ERP 推送订单备注
- 支持手动触发一次汇率刷新

## 12. 验收文档
- 生产上线检查：`docs/PRODUCTION-GO-LIVE-CHECKLIST.md`
- 一键自测 Runbook：`docs/SELF-TEST-RUNBOOK.md`
