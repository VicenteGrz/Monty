#include <iostream>
#include <iomanip>

float calcularETA(float ETA) {
    float totalMinutes = ETA * 20.0f; 
    return totalMinutes;
}
int main() {
    float ETA;
    std::cout << "Intervalo";
    std::cin >> progress;
    if (progress < 0.0f || progress > 1.0f || static_cast<int>(progress * 10) != progreso * 10) {
        std::cerr << "error ." << std::endl;
        return 1;
    }
    float eta = calculateETA(progress);
    std::cout << std::fixed << std::setprecision(2);
    std::cout << "tiempo añadido " << eta << " minutos." << std::endl;
    return 0;
}
