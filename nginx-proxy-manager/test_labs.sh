#!/bin/bash
# Comprehensive lab endpoint tester
# Tests both proxy and direct access, checks CSS/JS/API resource loading

DOMAIN="labs.ids-cybersec.com"
PROXY_BASE="https://${DOMAIN}/lab"
DIRECT_BASE="http://localhost"
RESULTS_DIR="/tmp/lab_test_results"
mkdir -p "$RESULTS_DIR"

# All ports to test
PORTS=(2222 5001 5002 5003 5005 5006 6001 6002 6003 6004 8001 8020 8021 8022 8023 8024 8025 8026 8027 8028 8029 8030 8031 8032 8033 8034 8035 8036 8037 8038 8039 8040 8041 8042 8043 8050 8051 8052 8053 8054 8055 8056 8060 8061 8062 8063)

CURL_OPTS="-skL --connect-timeout 5 --max-time 15"

extract_resources() {
    local html_file="$1"
    local port="$2"

    # Extract CSS references: href="..." from <link>, @import url(...), <style> tags
    css_hrefs=$(grep -oiP '(?:href=["'"'"'])([^"'"'"']+\.css(?:\?[^"'"'"']*)?)(?:["'"'"'])' "$html_file" 2>/dev/null | sed 's/href=["'"'"']//;s/["'"'"']//g' | sort -u)

    css_imports=$(grep -oiP '@import\s+(?:url\()?["'"'"']?([^"'"'"'\);]+\.css(?:\?[^"'"'"'\);]*)?)["'"'"']?\)?' "$html_file" 2>/dev/null | sed 's/.*@import[^a-zA-Z0-9]*//;s/["'"'"';()]//g' | sort -u)

    # Extract JS references: src="..." from <script>
    js_srcs=$(grep -oiP '(?:src=["'"'"'])([^"'"'"']+\.js(?:\?[^"'"'"']*)?)(?:["'"'"'])' "$html_file" 2>/dev/null | sed 's/src=["'"'"']//;s/["'"'"']//g' | sort -u)

    # Extract image references: src="..." from <img>
    img_srcs=$(grep -oiP '(?:src=["'"'"'])([^"'"'"']+\.(?:png|jpg|jpeg|gif|svg|ico|webp)(?:\?[^"'"'"']*)?)(?:["'"'"'])' "$html_file" 2>/dev/null | sed 's/src=["'"'"']//;s/["'"'"']//g' | sort -u)

    # Extract API calls from JS/fetch/ajax patterns
    api_refs=$(grep -oiP '(?:fetch|ajax|axios|XMLHttpRequest|\.get|\.post)\s*\(\s*["'"'"']?(/[a-zA-Z0-9_/.-]+)' "$html_file" 2>/dev/null | sed 's/.*(["'"'"']//;s/["'"'"']//;s/).*//' | sort -u)

    # Extract action= or form action references
    form_actions=$(grep -oiP '(?:action=["'"'"'])([^"'"'"']+)(?:["'"'"'])' "$html_file" 2>/dev/null | sed 's/action=["'"'"']//;s/["'"'"']//g' | sort -u)

    # Combine CSS
    css_all=$(echo -e "$css_hrefs\n$css_imports" | grep -v '^$' | sort -u)

    echo "CSS_REFS:$css_all"
    echo "JS_REFS:$js_srcs"
    echo "IMG_REFS:$img_srcs"
    echo "API_REFS:$api_refs"
    echo "FORM_REFS:$form_actions"
}

classify_path() {
    local path="$1"
    # Absolute path starts with /
    if [[ "$path" =~ ^/ ]]; then
        echo "ABSOLUTE"
    elif [[ "$path" =~ ^https?:// ]]; then
        echo "EXTERNAL"
    elif [[ "$path" =~ ^// ]]; then
        echo "PROTOCOL_RELATIVE"
    else
        echo "RELATIVE"
    fi
}

test_port() {
    local port="$1"
    local proxy_html="${RESULTS_DIR}/proxy_${port}.html"
    local direct_html="${RESULTS_DIR}/direct_${port}.html"
    local report_file="${RESULTS_DIR}/report_${port}.txt"

    > "$report_file"

    echo "=== PORT $port ===" >> "$report_file"

    # ---- PROXY ACCESS ----
    proxy_status=$(curl $CURL_OPTS -o "$proxy_html" -w "%{http_code}|%{size_download}|%{content_type}" "${PROXY_BASE}/${port}/" 2>/dev/null)
    proxy_http_code=$(echo "$proxy_status" | cut -d'|' -f1)
    proxy_size=$(echo "$proxy_status" | cut -d'|' -f2)
    proxy_ct=$(echo "$proxy_status" | cut -d'|' -f3)

    echo "PROXY_ACCESS:" >> "$report_file"
    echo "  URL: ${PROXY_BASE}/${port}/" >> "$report_file"
    echo "  HTTP: $proxy_http_code | Size: $proxy_size bytes | Type: $proxy_ct" >> "$report_file"

    # ---- DIRECT ACCESS ----
    direct_status=$(curl $CURL_OPTS -o "$direct_html" -w "%{http_code}|%{size_download}|%{content_type}" "${DIRECT_BASE}:${port}/" 2>/dev/null)
    direct_http_code=$(echo "$direct_status" | cut -d'|' -f1)
    direct_size=$(echo "$direct_status" | cut -d'|' -f2)
    direct_ct=$(echo "$direct_status" | cut -d'|' -f3)

    echo "DIRECT_ACCESS:" >> "$report_file"
    echo "  URL: ${DIRECT_BASE}:${port}/" >> "$report_file"
    echo "  HTTP: $direct_http_code | Size: $direct_size bytes | Type: $direct_ct" >> "$report_file"

    # Check if HTML was actually returned
    if [ "$proxy_http_code" = "000" ]; then
        echo "PROXY_RESULT: UNREACHABLE" >> "$report_file"
        echo "---" >> "$report_file"
        return
    fi

    if [ "$direct_http_code" = "000" ]; then
        echo "DIRECT_RESULT: UNREACHABLE" >> "$report_file"
    fi

    # Check if it's actually HTML
    proxy_is_html="NO"
    if file "$proxy_html" 2>/dev/null | grep -qi "text\|html"; then
        proxy_is_html="YES"
    elif head -c 200 "$proxy_html" 2>/dev/null | grep -qi "<!doctype\|<html\|<head"; then
        proxy_is_html="YES"
    fi

    direct_is_html="NO"
    if file "$direct_html" 2>/dev/null | grep -qi "text\|html"; then
        direct_is_html="YES"
    elif head -c 200 "$direct_html" 2>/dev/null | grep -qi "<!doctype\|<html\|<head"; then
        direct_is_html="YES"
    fi

    echo "PROXY_IS_HTML: $proxy_is_html" >> "$report_file"
    echo "DIRECT_IS_HTML: $direct_is_html" >> "$report_file"

    # Compare HTML content
    if [ "$proxy_is_html" = "YES" ] && [ "$direct_is_html" = "YES" ]; then
        if diff -q "$proxy_html" "$direct_html" > /dev/null 2>&1; then
            echo "HTML_MATCH: YES (identical)" >> "$report_file"
        else
            echo "HTML_MATCH: NO (different content)" >> "$report_file"
        fi
    fi

    # ---- ANALYZE RESOURCES FROM PROXY HTML ----
    if [ "$proxy_is_html" = "YES" ]; then
        resources=$(extract_resources "$proxy_html" "$port")

        css_refs=$(echo "$resources" | grep "^CSS_REFS:" | sed 's/CSS_REFS://')
        js_refs=$(echo "$resources" | grep "^JS_REFS:" | sed 's/JS_REFS://')
        img_refs=$(echo "$resources" | grep "^IMG_REFS:" | sed 's/IMG_REFS://')
        api_refs=$(echo "$resources" | grep "^API_REFS:" | sed 's/API_REFS://')
        form_refs=$(echo "$resources" | grep "^FORM_REFS:" | sed 's/FORM_REFS://')

        echo "" >> "$report_file"
        echo "--- RESOURCE ANALYSIS ---" >> "$report_file"

        # CSS Analysis
        echo "" >> "$report_file"
        echo "CSS_RESOURCES:" >> "$report_file"
        if [ -z "$css_refs" ]; then
            echo "  (none found)" >> "$report_file"
        else
            echo "$css_refs" | while read -r css; do
                [ -z "$css" ] && continue
                ptype=$(classify_path "$css")
                echo "  $css [$ptype]" >> "$report_file"

                # Try to load via proxy
                if [ "$ptype" = "ABSOLUTE" ]; then
                    proxy_url="${PROXY_BASE}/${port}${css}"
                    # Also try without the leading slash duplication
                    proxy_url2="${PROXY_BASE}/${port}$(echo "$css" | sed 's/^\///')"
                elif [ "$ptype" = "RELATIVE" ]; then
                    proxy_url="${PROXY_BASE}/${port}/${css}"
                    proxy_url2=""
                elif [ "$ptype" = "EXTERNAL" ] || [ "$ptype" = "PROTOCOL_RELATIVE" ]; then
                    echo "    -> External, not proxied" >> "$report_file"
                    continue
                fi

                if [ -n "$proxy_url" ]; then
                    css_status=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "$proxy_url" 2>/dev/null)
                    echo "    Proxy ($proxy_url): $css_status" >> "$report_file"
                fi
                if [ -n "$proxy_url2" ]; then
                    css_status2=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "$proxy_url2" 2>/dev/null)
                    if [ "$css_status2" != "$css_status" ]; then
                        echo "    Proxy alt ($proxy_url2): $css_status2" >> "$report_file"
                    fi
                fi

                # Try direct
                if [ "$ptype" = "ABSOLUTE" ]; then
                    direct_url="${DIRECT_BASE}:${port}${css}"
                elif [ "$ptype" = "RELATIVE" ]; then
                    direct_url="${DIRECT_BASE}:${port}/${css}"
                else
                    direct_url=""
                fi
                if [ -n "$direct_url" ]; then
                    direct_css_status=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "$direct_url" 2>/dev/null)
                    echo "    Direct ($direct_url): $direct_css_status" >> "$report_file"
                fi
            done
        fi

        # JS Analysis
        echo "" >> "$report_file"
        echo "JS_RESOURCES:" >> "$report_file"
        if [ -z "$js_refs" ]; then
            echo "  (none found)" >> "$report_file"
        else
            echo "$js_refs" | while read -r js; do
                [ -z "$js" ] && continue
                ptype=$(classify_path "$js")
                echo "  $js [$ptype]" >> "$report_file"

                if [ "$ptype" = "ABSOLUTE" ]; then
                    proxy_url="${PROXY_BASE}/${port}${js}"
                elif [ "$ptype" = "RELATIVE" ]; then
                    proxy_url="${PROXY_BASE}/${port}/${js}"
                elif [ "$ptype" = "EXTERNAL" ] || [ "$ptype" = "PROTOCOL_RELATIVE" ]; then
                    echo "    -> External, not proxied" >> "$report_file"
                    continue
                fi

                if [ -n "$proxy_url" ]; then
                    js_status=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "$proxy_url" 2>/dev/null)
                    echo "    Proxy ($proxy_url): $js_status" >> "$report_file"
                fi

                if [ "$ptype" = "ABSOLUTE" ]; then
                    direct_url="${DIRECT_BASE}:${port}${js}"
                elif [ "$ptype" = "RELATIVE" ]; then
                    direct_url="${DIRECT_BASE}:${port}/${js}"
                else
                    direct_url=""
                fi
                if [ -n "$direct_url" ]; then
                    direct_js_status=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "$direct_url" 2>/dev/null)
                    echo "    Direct ($direct_url): $direct_js_status" >> "$report_file"
                fi
            done
        fi

        # Image Analysis
        echo "" >> "$report_file"
        echo "IMG_RESOURCES:" >> "$report_file"
        if [ -z "$img_refs" ]; then
            echo "  (none found)" >> "$report_file"
        else
            echo "$img_refs" | while read -r img; do
                [ -z "$img" ] && continue
                ptype=$(classify_path "$img")
                echo "  $img [$ptype]" >> "$report_file"

                if [ "$ptype" = "ABSOLUTE" ]; then
                    proxy_url="${PROXY_BASE}/${port}${img}"
                elif [ "$ptype" = "RELATIVE" ]; then
                    proxy_url="${PROXY_BASE}/${port}/${img}"
                elif [ "$ptype" = "EXTERNAL" ] || [ "$ptype" = "PROTOCOL_RELATIVE" ]; then
                    echo "    -> External, not proxied" >> "$report_file"
                    continue
                fi

                if [ -n "$proxy_url" ]; then
                    img_status=$(curl $CURL_OPTS -o /dev/null -w "%{http_code}" "$proxy_url" 2>/dev/null)
                    echo "    Proxy ($proxy_url): $img_status" >> "$report_file"
                fi
            done
        fi

        # API Analysis
        echo "" >> "$report_file"
        echo "API_ENDPOINTS:" >> "$report_file"
        if [ -z "$api_refs" ]; then
            echo "  (none found)" >> "$report_file"
        else
            echo "$api_refs" | while read -r api; do
                [ -z "$api" ] && continue
                echo "  $api" >> "$report_file"
            done
        fi

        # Form Actions
        echo "" >> "$report_file"
        echo "FORM_ACTIONS:" >> "$report_file"
        if [ -z "$form_refs" ]; then
            echo "  (none found)" >> "$report_file"
        else
            echo "$form_refs" | while read -r fa; do
                [ -z "$fa" ] && continue
                ptype=$(classify_path "$fa")
                echo "  $fa [$ptype]" >> "$report_file"
            done
        fi

        # ---- BROKEN PATHS SUMMARY ----
        echo "" >> "$report_file"
        echo "ISSUES:" >> "$report_file"
        # Check for absolute paths that would break
        abs_css=$(echo "$css_refs" | grep '^/' 2>/dev/null)
        abs_js=$(echo "$js_refs" | grep '^/' 2>/dev/null)
        abs_img=$(echo "$img_refs" | grep '^/' 2>/dev/null)
        abs_forms=$(echo "$form_refs" | grep '^/' 2>/dev/null)

        abs_count=$(echo -e "$abs_css\n$abs_js\n$abs_img\n$abs_forms" | grep -c '^/' 2>/dev/null || echo 0)
        rel_count=$(echo -e "$css_refs\n$js_refs\n$img_refs" | grep -cv '^/\|^$\|^http' 2>/dev/null || echo 0)

        echo "  Absolute paths (broken via proxy): $abs_count" >> "$report_file"
        echo "  Relative paths (work via proxy): $rel_count" >> "$report_file"

        if [ $abs_count -gt 0 ]; then
            echo "  BROKEN_ABSOLUTE_PATHS:" >> "$report_file"
            for p in $abs_css $abs_js $abs_img $abs_forms; do
                [ -z "$p" ] && continue
                echo "    $p -> resolves to ${DOMAIN}${p} instead of ${DOMAIN}/lab/${port}${p}" >> "$report_file"
            done
        fi
    fi

    echo "---" >> "$report_file"
}

# Run tests
echo "Testing ${#PORTS[@]} lab ports..."
for port in "${PORTS[@]}"; do
    test_port "$port" &
done
wait

echo "All tests complete. Reports in $RESULTS_DIR/"
