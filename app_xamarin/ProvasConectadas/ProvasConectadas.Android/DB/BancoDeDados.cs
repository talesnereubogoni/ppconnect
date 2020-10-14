using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using Android.App;
using Android.Content;
using Android.OS;
using Android.Runtime;
using Android.Views;
using Android.Widget;

using ProvasConectadas.DB;
using Xamarin.Forms;
using System.IO;
using ProvasConectadas.Droid.DB;

[assembly: Dependency(typeof(BancoDeDados))]
namespace ProvasConectadas.Droid.DB
{
    public class BancoDeDados : I_Database
    {
        public string getPath(string nomedoarquivo)
        {
            string path = System.Environment.GetFolderPath(System.Environment.SpecialFolder.Personal);
            string arquivo = Path.Combine(path, nomedoarquivo);
            return arquivo;
        }
    }
}