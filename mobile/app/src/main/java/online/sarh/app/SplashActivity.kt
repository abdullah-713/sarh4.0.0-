package online.sarh.app

import android.annotation.SuppressLint
import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.LinearLayout
import android.widget.ProgressBar
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

/**
 * ══════════════════════════════════════════════════════════════
 *  SARH — شاشة البداية + فحص التحديثات
 * ══════════════════════════════════════════════════════════════
 *  1. تعرض شعار التطبيق لمدة قصيرة
 *  2. تتحقق من وجود تحديث جديد عبر API
 *  3. إذا وُجد تحديث → تعرض خيار التحميل
 *  4. إذا لم يوجد (أو لا اتصال) → تنتقل مباشرة
 * ══════════════════════════════════════════════════════════════
 */
class SplashActivity : AppCompatActivity() {

    private lateinit var progressBar: ProgressBar
    private lateinit var updateLayout: LinearLayout
    private lateinit var updateMessage: TextView
    private lateinit var btnUpdate: Button
    private lateinit var btnSkip: Button

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)

        progressBar = findViewById(R.id.splashProgress)
        updateLayout = findViewById(R.id.updateLayout)
        updateMessage = findViewById(R.id.updateMessage)
        btnUpdate = findViewById(R.id.btnUpdate)
        btnSkip = findViewById(R.id.btnSkip)

        btnUpdate.setOnClickListener {
            // فتح رابط التحميل في المتصفح
            val url = btnUpdate.tag as? String ?: "${BuildConfig.BASE_URL}/app/sarh.apk"
            startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
        }

        btnSkip.setOnClickListener {
            launchMain()
        }

        checkForUpdate()
    }

    @SuppressLint("SetTextI18n")
    private fun checkForUpdate() {
        Thread {
            try {
                val url = URL(BuildConfig.VERSION_CHECK_URL)
                val conn = url.openConnection() as HttpURLConnection
                conn.connectTimeout = 5000
                conn.readTimeout = 5000
                conn.requestMethod = "GET"

                if (conn.responseCode == 200) {
                    val response = conn.inputStream.bufferedReader().readText()
                    val json = JSONObject(response)

                    val serverVersionCode = json.optInt("version_code", 0)
                    val serverVersionName = json.optString("version_name", "")
                    val downloadUrl = json.optString("download_url", "")
                    val forceUpdate = json.optBoolean("force_update", false)
                    val changelog = json.optString("changelog", "")

                    val currentVersionCode = BuildConfig.VERSION_CODE

                    if (serverVersionCode > currentVersionCode) {
                        // يوجد تحديث
                        runOnUiThread {
                            progressBar.visibility = View.GONE
                            updateLayout.visibility = View.VISIBLE

                            updateMessage.text = buildString {
                                append("يتوفر إصدار جديد: $serverVersionName\n")
                                append("الإصدار الحالي: ${BuildConfig.VERSION_NAME}\n")
                                if (changelog.isNotEmpty()) {
                                    append("\n$changelog")
                                }
                            }

                            btnUpdate.tag = downloadUrl
                            btnSkip.visibility = if (forceUpdate) View.GONE else View.VISIBLE
                        }
                    } else {
                        // لا يوجد تحديث → الانتقال مباشرة
                        runOnUiThread { launchMain() }
                    }
                } else {
                    // خطأ في الاتصال → الانتقال مباشرة
                    runOnUiThread { launchMain() }
                }
                conn.disconnect()
            } catch (e: Exception) {
                // لا اتصال → الانتقال مباشرة
                runOnUiThread { launchMain() }
            }
        }.start()
    }

    private fun launchMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish()
    }
}
