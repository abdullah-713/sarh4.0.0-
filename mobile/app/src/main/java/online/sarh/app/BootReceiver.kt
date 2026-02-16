package online.sarh.app

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent

/**
 * ══════════════════════════════════════════════════════════════
 *  SARH — التشغيل التلقائي عند إعادة تشغيل الجهاز
 * ══════════════════════════════════════════════════════════════
 *  يستقبل إشارة BOOT_COMPLETED ويفتح التطبيق تلقائياً.
 *  يمكن تعطيل هذه الميزة من إعدادات التطبيق.
 * ══════════════════════════════════════════════════════════════
 */
class BootReceiver : BroadcastReceiver() {

    override fun onReceive(context: Context?, intent: Intent?) {
        if (intent?.action == Intent.ACTION_BOOT_COMPLETED) {
            val prefs = context?.getSharedPreferences("sarh_prefs", Context.MODE_PRIVATE)
            val autoStart = prefs?.getBoolean("auto_start", false) ?: false

            if (autoStart) {
                val launchIntent = Intent(context, SplashActivity::class.java).apply {
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                context?.startActivity(launchIntent)
            }
        }
    }
}
