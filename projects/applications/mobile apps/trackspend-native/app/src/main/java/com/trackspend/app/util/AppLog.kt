package com.trackspend.app.util

import android.util.Log
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

object AppLog {
    const val TAG = "TrackSpend"
    private const val MAX_ENTRIES = 500
    private val buffer = ArrayDeque<String>()
    private val lock = Any()

    fun i(msg: String) = post("I", msg)
    fun d(msg: String) = post("D", msg)
    fun w(msg: String) = post("W", msg)
    fun e(msg: String, t: Throwable? = null) {
        post("E", if (t != null) "$msg | ${t::class.simpleName}: ${t.message}" else msg)
    }

    private fun post(level: String, msg: String) {
        val line = "[$level] ${ts()} $msg"
        Log.println(toLogLevel(level), TAG, msg)
        synchronized(lock) {
            buffer.addLast(line)
            while (buffer.size > MAX_ENTRIES) buffer.removeFirst()
        }
    }

    fun list(): List<String> = synchronized(lock) { buffer.toList() }

    fun clear() = synchronized(lock) { buffer.clear() }

    private fun ts(): String = SimpleDateFormat("HH:mm:ss", Locale.US).format(Date())

    private fun toLogLevel(l: String): Int = when (l) {
        "D" -> Log.DEBUG
        "W" -> Log.WARN
        "E" -> Log.ERROR
        else -> Log.INFO
    }
}