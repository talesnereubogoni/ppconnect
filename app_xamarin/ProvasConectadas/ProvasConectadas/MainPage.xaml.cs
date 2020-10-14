using Plugin.TextToSpeech;
using ProvasConectadas.View;
using ProvasConectadas.View.Admin;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Xamarin.Essentials;
using Xamarin.Forms;

namespace ProvasConectadas
{
    // Learn more about making custom code visible in the Xamarin.Forms previewer
    // by visiting https://aka.ms/xamarinforms-previewer
    [DesignTimeVisible(false)]
    public partial class MainPage : ContentPage
    {
        public static double ScreenWidth { set; get; }
        public static double ScreenHeight { set; get; }
        public MainPage()
        {
            ScreenWidth = DeviceDisplay.MainDisplayInfo.Width;
            ScreenHeight = DeviceDisplay.MainDisplayInfo.Height;
            InitializeComponent();
            background_img.HeightRequest = ScreenHeight;
            background_img.WidthRequest = ScreenWidth;
            DadosAdmin.conexao = "http://192.168.43.199/";// "http://ppconnect.com.br/";
        }

        private async void Bt_aluno_Clicked(object sender, EventArgs e)
        {
            await Navigation.PushAsync(new EntradaAluno());
        }

        private async void Bt_admin_Clicked(object sender, EventArgs e)
        {
            await Navigation.PushAsync(new EntradaAdmin());
        }

        private async void Bt_tutor_Clicked(object sender, EventArgs e)
        {
            await Navigation.PushAsync(new EntradaTutor());
        }
    }
}
