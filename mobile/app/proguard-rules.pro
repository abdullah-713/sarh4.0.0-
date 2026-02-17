# ProGuard rules for SarhIndex App
-keepattributes *Annotation*
-keepattributes SourceFile,LineNumberTable

# WebView
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}

# Keep the app's main classes
-keep class online.sarh.app.** { *; }
