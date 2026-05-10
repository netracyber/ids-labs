#!/bin/bash
# Clean analysis script - reads raw HTML from both proxy and direct access
# Produces a clear per-port report

DOMAIN="labs.ids-cybersec.com"
PROXY_BASE="https://${DOMAIN}/lab"
DIRECT_BASE="http://localhost"
RESULTS_DIR="/tmp/lab_test_results2"
mkdir -p "$RESULTS_DIR"

PORTS=(2222 5001 5002 5003 5005 5006 6001 6002 6003 6004 8001 8020 8021 8022 8023 8024 8025 8026 8027 8028 8029 8030 8031 8032 8033 8034 8035 8036 8037 8038 8039 8040 8041 8042 8043 8050 8051 8052 8053 8054 8055 8056 8060 8061 8062 8063)

CURL_OPTS="-skL --connect-timeout 5 --max-time 15"

SUMMARY_FILE="${RESULTS_DIR}/summary.txt"
> "$SUMMARY_FILE"

for PORT in "${PORTS[@]}"; do
    echo "===== PORT $PORT =====" | tee -a "$SUMMARY_FILE"

    PROXY_HTML="${RESULTS_DIR}/proxy_${PORT}.html"
    DIRECT_HTML="${RESULTS_DIR}/direct_${PORT}.html"

    # Fetch proxy
    PROXY_CODE=$(curl $CURL_OPTS -o "$PROXY_HTML" -w "%{http_code}" "${PROXY_BASE}/${PORT}/" 2>/dev/null)
    # Fetch direct
    DIRECT_CODE=$(curl $CURL_OPTS -o "$DIRECT_HTML" -w "%{http_code}" "${DIRECT_BASE}:${PORT}/" 2>/dev/null)

    echo "  Proxy:  HTTP $PROXY_CODE | Direct: HTTP $DIRECT_CODE" | tee -a "$SUMMARY_FILE"

    if [ "$PROXY_CODE" = "000" ] && [ "$DIRECT_CODE" = "000" ]; then
        echo "  STATUS: BOTH UNREACHABLE" | tee -a "$SUMMARY_FILE"
        echo "" | tee -a "$SUMMARY_FILE"
        continue
    fi

    # Check if proxy returned HTML
    PROXY_HTML_FLAG=0
    if [ -f "$PROXY_HTML" ] && head -c 500 "$PROXY_HTML" 2>/dev/null | grep -qi '<html\|<!doctype\|<head\|<body\|<link\|<script'; then
        PROXY_HTML_FLAG=1
    fi

    DIRECT_HTML_FLAG=0
    if [ -f "$DIRECT_HTML" ] && head -c 500 "$DIRECT_HTML" 2>/dev/null | grep -qi '<html\|<!doctype\|<head\|<body\|<link\|<script'; then
        DIRECT_HTML_FLAG=1
    fi

    # Determine which HTML to analyze (prefer proxy)
    ANALYZE_FILE=""
    if [ $PROXY_HTML_FLAG -eq 1 ]; then
        ANALYZE_FILE="$PROXY_HTML"
        echo "  HTML: Proxy returned valid HTML" | tee -a "$SUMMARY_FILE"
    elif [ $DIRECT_HTML_FLAG -eq 1 ]; then
        ANALYZE_FILE="$DIRECT_HTML"
        echo "  HTML: Only direct returned valid HTML (proxy may be broken)" | tee -a "$SUMMARY_FILE"
    else
        echo "  HTML: Neither returned valid HTML" | tee -a "$SUMMARY_FILE"
        # Show what was returned
        if [ -f "$PROXY_HTML" ]; then
            echo "    Proxy content (first 200 chars): $(head -c 200 "$PROXY_HTML" 2>/dev/null | tr '\n' ' ')" | tee -a "$SUMMARY_FILE"
        fi
        echo "" | tee -a "$SUMMARY_FILE"
        continue
    fi

    # ---- EXTRACT ALL RESOURCE REFERENCES ----
    ABS_CSS=$(grep -oiP 'href=["'"'"']\K/[^"'"'"']+\.css[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)
    REL_CSS=$(grep -oiP 'href=["'"'"']\K(?!/|https?://|//)[^"'"'"']+\.css[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)
    EXT_CSS=$(grep -oiP 'href=["'"'"']\K(https?://|//)[^"'"'"']+\.css[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)

    ABS_JS=$(grep -oiP 'src=["'"'"']\K/[^"'"'"']+\.js[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)
    REL_JS=$(grep -oiP 'src=["'"'"']\K(?!/|https?://|//)[^"'"'"']+\.js[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)
    EXT_JS=$(grep -oiP 'src=["'"'"']\K(https?://|//)[^"'"'"']+\.js[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)

    ABS_IMG=$(grep -oiP 'src=["'"'"']\K/[^"'"'"']+\.(png|jpg|jpeg|gif|svg|ico|webp)[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)
    REL_IMG=$(grep -oiP 'src=["'"'"']\K(?!/|https?://|//)[^"'"'"']+\.(png|jpg|jpeg|gif|svg|ico|webp)[^"'"'"']*' "$ANALYZE_FILE" 2>/dev/null | sort -u)

    API_REFS=$(grep -oiP '(?:fetch|\.get|\.post|ajax)\s*\(\s*["'"'"']?\K/[a-zA-Z0-9_/.-]+' "$ANALYZE_FILE" 2>/dev/null | sort -u)

    FORM_ACTIONS=$(grep -oiP 'action=["'"'"']\K[^"'"'"']+' "$ANALYZE_FILE" 2>/dev/null | sort -u)

    # ---- REPORT ----
    ABS_TOTAL=0
    REL_TOTAL=0
    ISSUES=""

    if [ -n "$ABS_CSS" ]; then
        echo "  ABSOLUTE CSS (BROKEN via proxy):" | tee -a "$SUMMARY_FILE"
        for f in $ABS_CSS; do
            echo "    $f -> browser resolves to ${DOMAIN}${f} (404)" | tee -a "$SUMMARY_FILE"
            # Test if it loads via proxy path
            PSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${PROXY_BASE}/${PORT}${f}" 2>/dev/null)
            DSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${DIRECT_BASE}:${PORT}${f}" 2>/dev/null)
            echo "      proxy=${PROXY_BASE}/${PORT}${f} -> $PSTATUS | direct=${DIRECT_BASE}:${PORT}${f} -> $DSTATUS" | tee -a "$SUMMARY_FILE"
            ABS_TOTAL=$((ABS_TOTAL + 1))
        done
    fi

    if [ -n "$REL_CSS" ]; then
        echo "  RELATIVE CSS (works via proxy):" | tee -a "$SUMMARY_FILE"
        for f in $REL_CSS; do
            PSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${PROXY_BASE}/${PORT}/${f}" 2>/dev/null)
            echo "    $f -> proxy status: $PSTATUS" | tee -a "$SUMMARY_FILE"
            REL_TOTAL=$((REL_TOTAL + 1))
        done
    fi

    if [ -n "$EXT_CSS" ]; then
        echo "  EXTERNAL CSS:" | tee -a "$SUMMARY_FILE"
        for f in $EXT_CSS; do
            echo "    $f" | tee -a "$SUMMARY_FILE"
        done
    fi

    if [ -n "$ABS_JS" ]; then
        echo "  ABSOLUTE JS (BROKEN via proxy):" | tee -a "$SUMMARY_FILE"
        for f in $ABS_JS; do
            echo "    $f -> browser resolves to ${DOMAIN}${f} (404)" | tee -a "$SUMMARY_FILE"
            PSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${PROXY_BASE}/${PORT}${f}" 2>/dev/null)
            DSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${DIRECT_BASE}:${PORT}${f}" 2>/dev/null)
            echo "      proxy=${PROXY_BASE}/${PORT}${f} -> $PSTATUS | direct=${DIRECT_BASE}:${PORT}${f} -> $DSTATUS" | tee -a "$SUMMARY_FILE"
            ABS_TOTAL=$((ABS_TOTAL + 1))
        done
    fi

    if [ -n "$REL_JS" ]; then
        echo "  RELATIVE JS (works via proxy):" | tee -a "$SUMMARY_FILE"
        for f in $REL_JS; do
            PSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${PROXY_BASE}/${PORT}/${f}" 2>/dev/null)
            echo "    $f -> proxy status: $PSTATUS" | tee -a "$SUMMARY_FILE"
            REL_TOTAL=$((REL_TOTAL + 1))
        done
    fi

    if [ -n "$EXT_JS" ]; then
        echo "  EXTERNAL JS:" | tee -a "$SUMMARY_FILE"
        for f in $EXT_JS; do
            echo "    $f" | tee -a "$SUMMARY_FILE"
        done
    fi

    if [ -n "$ABS_IMG" ]; then
        echo "  ABSOLUTE IMAGES (BROKEN via proxy):" | tee -a "$SUMMARY_FILE"
        for f in $ABS_IMG; do
            echo "    $f -> browser resolves to ${DOMAIN}${f} (404)" | tee -a "$SUMMARY_FILE"
            PSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${PROXY_BASE}/${PORT}${f}" 2>/dev/null)
            echo "      proxy=${PROXY_BASE}/${PORT}${f} -> $PSTATUS" | tee -a "$SUMMARY_FILE"
            ABS_TOTAL=$((ABS_TOTAL + 1))
        done
    fi

    if [ -n "$REL_IMG" ]; then
        echo "  RELATIVE IMAGES (works via proxy):" | tee -a "$SUMMARY_FILE"
        for f in $REL_IMG; do
            PSTATUS=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "${PROXY_BASE}/${PORT}/${f}" 2>/dev/null)
            echo "    $f -> proxy status: $PSTATUS" | tee -a "$SUMMARY_FILE"
            REL_TOTAL=$((REL_TOTAL + 1))
        done
    fi

    if [ -n "$API_REFS" ]; then
        echo "  API ENDPOINTS (in HTML/JS):" | tee -a "$SUMMARY_FILE"
        for f in $API_REFS; do
            echo "    $f (absolute -> BROKEN via proxy)" | tee -a "$SUMMARY_FILE"
            ABS_TOTAL=$((ABS_TOTAL + 1))
        done
    fi

    if [ -n "$FORM_ACTIONS" ]; then
        echo "  FORM ACTIONS:" | tee -a "$SUMMARY_FILE"
        for f in $FORM_ACTIONS; do
            if [[ "$f" =~ ^/ ]]; then
                echo "    $f (absolute -> BROKEN via proxy)" | tee -a "$SUMMARY_FILE"
                ABS_TOTAL=$((ABS_TOTAL + 1))
            else
                echo "    $f (relative -> works via proxy)" | tee -a "$SUMMARY_FILE"
                REL_TOTAL=$((REL_TOTAL + 1))
            fi
        done
    fi

    echo "  SUMMARY: $ABS_TOTAL broken absolute paths, $REL_TOTAL working relative paths" | tee -a "$SUMMARY_FILE"
    if [ $ABS_TOTAL -gt 0 ]; then
        echo "  VERDICT: BROKEN - Needs sub_filter or base tag fix" | tee -a "$SUMMARY_FILE"
    else
        echo "  VERDICT: OK - All resources use relative/external paths" | tee -a "$SUMMARY_FILE"
    fi

    echo "" | tee -a "$SUMMARY_FILE"
done

echo "Done. Summary at $SUMMARY_FILE"
