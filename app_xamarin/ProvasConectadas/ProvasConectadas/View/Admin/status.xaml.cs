using ProvasConectadas.DB;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View.Admin
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class status : ContentPage
    {
        public status()
        {
            InitializeComponent();
            Database db = new Database();
            listadisciplinas.ItemsSource = db.SelecionarStatusProvas();
        }
    }
}