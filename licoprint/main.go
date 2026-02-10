package main

import (
	"embed"
	"encoding/json"
	"fmt"
	"html/template"
	"log"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strings"
	"sync"
)

//go:embed templates/*
var templateFS embed.FS

//go:embed static/*
var staticFS embed.FS

// Config estructura de configuración
type Config struct {
	PrinterName   string `json:"printer_name"`
	PrinterType   string `json:"printer_type"`
	PaperSize     string `json:"paper_size"`
	AutoCut       bool   `json:"auto_cut"`
	OpenDrawer    bool   `json:"open_drawer"`
	CharWidth     int    `json:"char_width"`
}

// PrintRequest estructura para solicitudes de impresión
type PrintRequest struct {
	Type    string `json:"type"`    // "raw", "text", "html"
	Content string `json:"content"` // Contenido a imprimir
	Copies  int    `json:"copies"`
}

var (
	config     Config
	configFile string
	configMu   sync.RWMutex
	printers   []string
)

func main() {
	// Configurar archivo de configuración
	configDir, _ := os.UserConfigDir()
	configFile = filepath.Join(configDir, "LicoPrint", "config.json")

	// Cargar configuración
	loadConfig()

	// Detectar impresoras
	printers = detectPrinters()

	// Rutas
	http.HandleFunc("/", handleIndex)
	http.HandleFunc("/api/config", handleConfig)
	http.HandleFunc("/api/printers", handlePrinters)
	http.HandleFunc("/api/print", handlePrint)
	http.HandleFunc("/api/test", handleTestPrint)
	http.Handle("/static/", http.FileServer(http.FS(staticFS)))

	port := "2026"
	fmt.Println("╔════════════════════════════════════════╗")
	fmt.Println("║         LicoPrint v1.0.0               ║")
	fmt.Println("║   Servicio de Impresión Local          ║")
	fmt.Println("╠════════════════════════════════════════╣")
	fmt.Printf("║   Servidor: http://localhost:%s       ║\n", port)
	fmt.Println("║   Presiona Ctrl+C para detener         ║")
	fmt.Println("╚════════════════════════════════════════╝")

	// Abrir navegador automáticamente
	go openBrowser("http://localhost:" + port)

	log.Fatal(http.ListenAndServe(":"+port, nil))
}

func loadConfig() {
	configMu.Lock()
	defer configMu.Unlock()

	// Valores por defecto
	config = Config{
		PrinterName: "",
		PrinterType: "thermal",
		PaperSize:   "80mm",
		AutoCut:     true,
		OpenDrawer:  false,
		CharWidth:   48,
	}

	// Crear directorio si no existe
	os.MkdirAll(filepath.Dir(configFile), 0755)

	// Leer archivo si existe
	data, err := os.ReadFile(configFile)
	if err == nil {
		json.Unmarshal(data, &config)
	}
}

func saveConfig() error {
	configMu.RLock()
	defer configMu.RUnlock()

	data, err := json.MarshalIndent(config, "", "  ")
	if err != nil {
		return err
	}

	return os.WriteFile(configFile, data, 0644)
}

func detectPrinters() []string {
	var printers []string

	if runtime.GOOS == "windows" {
		// Usar PowerShell para detectar impresoras en Windows
		cmd := exec.Command("powershell", "-Command", "Get-Printer | Select-Object -ExpandProperty Name")
		output, err := cmd.Output()
		if err == nil {
			lines := strings.Split(string(output), "\n")
			for _, line := range lines {
				line = strings.TrimSpace(line)
				if line != "" {
					printers = append(printers, line)
				}
			}
		}
	} else {
		// Linux/Mac - usar lpstat
		cmd := exec.Command("lpstat", "-p")
		output, err := cmd.Output()
		if err == nil {
			lines := strings.Split(string(output), "\n")
			for _, line := range lines {
				if strings.HasPrefix(line, "printer ") {
					parts := strings.Fields(line)
					if len(parts) >= 2 {
						printers = append(printers, parts[1])
					}
				}
			}
		}
	}

	return printers
}

func handleIndex(w http.ResponseWriter, r *http.Request) {
	if r.URL.Path != "/" {
		http.NotFound(w, r)
		return
	}

	tmpl, err := template.ParseFS(templateFS, "templates/index.html")
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	configMu.RLock()
	data := struct {
		Config   Config
		Printers []string
	}{
		Config:   config,
		Printers: printers,
	}
	configMu.RUnlock()

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	tmpl.Execute(w, data)
}

func handleConfig(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type")

	if r.Method == "OPTIONS" {
		return
	}

	if r.Method == "GET" {
		configMu.RLock()
		json.NewEncoder(w).Encode(config)
		configMu.RUnlock()
		return
	}

	if r.Method == "POST" {
		var newConfig Config
		if err := json.NewDecoder(r.Body).Decode(&newConfig); err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
			return
		}

		configMu.Lock()
		config = newConfig
		configMu.Unlock()

		if err := saveConfig(); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		json.NewEncoder(w).Encode(map[string]string{"status": "ok"})
		return
	}

	http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
}

func handlePrinters(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	if r.URL.Query().Get("refresh") == "1" {
		printers = detectPrinters()
	}

	json.NewEncoder(w).Encode(printers)
}

func handlePrint(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")
	w.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS")
	w.Header().Set("Access-Control-Allow-Headers", "Content-Type")

	if r.Method == "OPTIONS" {
		return
	}

	if r.Method != "POST" {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req PrintRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
			"error":   err.Error(),
		})
		return
	}

	configMu.RLock()
	printerName := config.PrinterName
	configMu.RUnlock()

	if printerName == "" {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
			"error":   "No hay impresora configurada",
		})
		return
	}

	err := printContent(printerName, req)
	if err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
			"error":   err.Error(),
		})
		return
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
	})
}

func handleTestPrint(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")

	configMu.RLock()
	printerName := config.PrinterName
	paperSize := config.PaperSize
	autoCut := config.AutoCut
	charWidth := config.CharWidth
	configMu.RUnlock()

	if printerName == "" {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
			"error":   "No hay impresora configurada",
		})
		return
	}

	// Generar ticket de prueba
	width := charWidth
	if paperSize == "58mm" && width > 32 {
		width = 32
	}

	line := strings.Repeat("=", width)
	content := fmt.Sprintf(`%s
%s
%s

Impresora: %s
Papel: %s
Ancho: %d caracteres
Corte auto: %v

%s
   IMPRESION CORRECTA!
%s

LicoPrint v1.0.0

`,
		line,
		centerText("PRUEBA DE IMPRESION", width),
		line,
		printerName,
		paperSize,
		width,
		autoCut,
		line,
		line,
	)

	req := PrintRequest{
		Type:    "text",
		Content: content,
		Copies:  1,
	}

	err := printContent(printerName, req)
	if err != nil {
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
			"error":   err.Error(),
		})
		return
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"success": true,
	})
}

func printContent(printerName string, req PrintRequest) error {
	if runtime.GOOS == "windows" {
		return printWindows(printerName, req)
	}
	return printUnix(printerName, req)
}

func printWindows(printerName string, req PrintRequest) error {
	// Crear archivo temporal
	tmpFile, err := os.CreateTemp("", "licoprint_*.txt")
	if err != nil {
		return err
	}
	defer os.Remove(tmpFile.Name())

	// Escribir contenido
	tmpFile.WriteString(req.Content)
	tmpFile.Close()

	// Imprimir usando el comando print de Windows
	for i := 0; i < max(req.Copies, 1); i++ {
		cmd := exec.Command("powershell", "-Command",
			fmt.Sprintf(`Get-Content '%s' | Out-Printer -Name '%s'`, tmpFile.Name(), printerName))
		if err := cmd.Run(); err != nil {
			return fmt.Errorf("error al imprimir: %v", err)
		}
	}

	return nil
}

func printUnix(printerName string, req PrintRequest) error {
	for i := 0; i < max(req.Copies, 1); i++ {
		cmd := exec.Command("lp", "-d", printerName)
		cmd.Stdin = strings.NewReader(req.Content)
		if err := cmd.Run(); err != nil {
			return fmt.Errorf("error al imprimir: %v", err)
		}
	}
	return nil
}

func centerText(text string, width int) string {
	if len(text) >= width {
		return text
	}
	padding := (width - len(text)) / 2
	return strings.Repeat(" ", padding) + text
}

func max(a, b int) int {
	if a > b {
		return a
	}
	return b
}

func openBrowser(url string) {
	var cmd *exec.Cmd

	switch runtime.GOOS {
	case "windows":
		cmd = exec.Command("rundll32", "url.dll,FileProtocolHandler", url)
	case "darwin":
		cmd = exec.Command("open", url)
	default:
		cmd = exec.Command("xdg-open", url)
	}

	cmd.Start()
}
